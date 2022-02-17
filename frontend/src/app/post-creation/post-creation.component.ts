import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { PostService } from '../_services/post.service';

import * as cocoSsd from '@tensorflow-models/coco-ssd';
import '@tensorflow/tfjs';
import { CurrentUser, UserService } from '../_services/user.service';
import { RolesService, WithRolesInfo } from '../_services/roles.service';
import { EMPTY, mergeMap, tap } from 'rxjs';


@Component({
  selector: 'app-post-creation',
  templateUrl: './post-creation.component.html',
  styleUrls: ['./post-creation.component.css']
})

export class PostCreationComponent implements OnInit {
  form: any = {
    title: null,
    body: null,
  };
  isSuccessful = false;
  isSubmitPostFailed = false;
  errorMessage = '';
  serverErrors = { title: null, body: null };

  currentUser: (CurrentUser & Partial<WithRolesInfo>) | null = null;

  fileLoadedCallback: null | (() => void) = null;

  constructor(
    private postService: PostService,
    private userService: UserService,
    private rolesService: RolesService,
  ) { }

  ngOnInit(): void {
    this.userService.getCurrentUser().pipe(
      tap(user => this.currentUser = user),
      mergeMap(user => {
        if (!user) return EMPTY;
        return this.rolesService.getRolesInfo(user);
      }),
    ).subscribe();
  }

  selectedFile: null | File = null;
  fileData: Blob | null = null;
  previewImageUrl: string | null = null;

  processImage() {
    /** Store all blob data inside a data URL. */
    const blobToDataURL = (blob: Blob) =>
      new Promise<string>((resolve, reject) => {
        try {
          const reader = new FileReader();
          reader.onload = () => {
            resolve(reader.result as string);
          };
          reader.onerror = () => {
            reject(new Error('failed to convert image data to data URL'));
          };
          reader.readAsDataURL(blob);
        } catch (error) {
          reject(error);
        }
      });

    /** Create a new image tag that is not connected to the document and wait
     * until it has loaded. */
    const createLoadedImage = (imageURL: string) =>
      new Promise<HTMLImageElement>((resolve, reject) => {
        try {
          const output = new Image();
          output.src = imageURL;
          output.onload = () => {
            resolve(output);
          };
          output.onerror = () => {
            reject(new Error('failed to create image from data URL'));
          };
        } catch (error) {
          reject(error);
        }
      });

    /** Save canvas content as a blob. */
    const canvasToBlob = (canvas: HTMLCanvasElement) =>
      new Promise<Blob>((resolve, reject) => {
        try {
          canvas.toBlob((blob) => {
            if (blob) {
              resolve(blob);
            } else {
              reject(new Error('failed to convert canvas to blob'));
            }
          });
        } catch (error) {
          reject(error);
        }
      });

    /** Read binary data from blob. */
    const blobToArrayBuffer = (blob: Blob) => {
      return new Promise<ArrayBuffer>((resolve, reject) => {
        try {
          // let fileName = this.selectedFile.name;
          let myReader = new FileReader();
          // let fileType = inputValue.parentElement.id;

          myReader.onloadend = () => {
            resolve(myReader.result as ArrayBuffer);
          };
          myReader.onerror = () => {
            reject(new Error('failed to read data to array'));
          };

          myReader.readAsArrayBuffer(blob);
        } catch (error) {
          reject(error);
        }
      });
    };

    let canvas = document.getElementById('ai-canvas') as HTMLCanvasElement;
    let ctx = canvas.getContext('2d');

    const rotateAround = (rotation: number, x: number, y: number) => {
      if (!ctx) return;
      ctx.translate(x, y);
      ctx.rotate(rotation);
      ctx.translate(-x, -y);
    };
    const degreesToRadians = (degrees: number) => degrees * Math.PI / 180;
    const randomInt = (start: number, end: number) => Math.floor(Math.random() * (1 + end - start)) + start;
    const randomFloat = (start: number, end: number) => (Math.random() * (end - start)) + start;
    function chooseRandom<T>(array: T[]): T {
      return array[randomInt(0, array.length - 1)];
    }

    /** Create a temp canvas that is a copy of the current one, then clear the
     * current canvas. If the callback returns `true` then the temp canvas will
     * be drawn onto the current canvas.
     *
     * This allows us to set some effects or transformations on the context
     * object and then apply them to the whole image. */
    const withTempCanvas = (callback: (tempCanvas: HTMLCanvasElement) => boolean) => {
      // Create a temp canvas to store our data (because we need to clear the other box after rotation.
      const tempCanvas = document.createElement("canvas");
      const tempCtx = tempCanvas.getContext("2d");
      if (!tempCtx || !ctx) throw new Error('failed to get 2d context');

      tempCanvas.width = canvas.width;
      tempCanvas.height = canvas.height;
      // put our data onto the temp canvas
      tempCtx.drawImage(canvas, 0, 0, canvas.width, canvas.height);

      // Append for debugging purposes, just to show what the canvas did look like before the transforms.
      // document.body.appendChild(tempCanvas);

      // Save current context state:
      ctx.save();


      // Now clear the old image:
      // ctx.fillStyle = "#000";
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      const drawAll = callback(tempCanvas);

      if (drawAll) {
        // Finally draw the image data from the temp canvas.
        ctx.drawImage(tempCanvas, 0, 0, canvas.width, canvas.height);
      }

      // Restore context state that we previously saved:
      ctx.restore();
    };
    /** Zoom in on a specific area. */
    const zoomIn = (x: number, y: number, w: number, h: number) => {
      if (x < 0) x = 0;
      if (y < 0) y = 0;
      if (x > canvas.width) x = canvas.width;
      if (y > canvas.height) y = canvas.height;

      if (w > canvas.width - x) w = canvas.width - x;
      if (h > canvas.height - y) h = canvas.height - y;
      if (w < 0) w = 0;
      if (h < 0) h = 0;
      withTempCanvas((tempCanvas) => {
        if (!ctx) return true;

        canvas.width = w;
        canvas.height = h;
        ctx.drawImage(tempCanvas, x, y, w, h, 0, 0, w, h);

        // Did canvas redrawing ourself so don't do that for us:
        return false;
      });
    };
    /** Resize the image. */
    const resize = (w: number, h: number) => {
      withTempCanvas((tempCanvas) => {
        if (!ctx) return true;

        canvas.width = w;
        canvas.height = h;
        ctx.drawImage(tempCanvas, 0, 0, tempCanvas.width, tempCanvas.height, 0, 0, w, h);

        // Did canvas redrawing ourself so don't do that for us:
        return false;
      });
    };
    const scale = (times: number) => resize(canvas.width * times, canvas.height * times);
    const postProcessCanvas = (predictions: cocoSsd.DetectedObject[]) => {
      if (!ctx) throw new Error('failed to get 2d context');

      const originalW = canvas.width;
      const originalH = canvas.height;
      const scaleSimilarToOriginal = () => scale(Math.min(originalH / canvas.height, originalW / canvas.width));

      // Use AI:
      if (predictions.length > 0) {
        const [x, y, w, h] = chooseRandom(predictions).bbox;
        const spaceRightOf = canvas.width - x - w;
        const spaceBelowOf = canvas.height - y - h;

        let choices: (() => void)[] = [];
        const addChoice = (callback: () => void, times?: number) => {
          if (times !== undefined) for (let i = 0; i < times; i++) choices.push(callback);
          else choices.push(callback);
        };

        const objectMargin = 20;
        const minSize = 50;
        // Left of image
        if (x > minSize) {
          addChoice(() => {
            console.log('left');
            zoomIn(0, 0, x + objectMargin, canvas.height);
          });
        }
        // Right of image
        if (spaceRightOf > minSize) {
          addChoice(() => {
            console.log('right');
            zoomIn(x + w - objectMargin, 0, spaceRightOf + objectMargin, canvas.height);
          });
        }
        // Above image:
        if (y > minSize) {
          addChoice(() => {
            console.log('top');
            zoomIn(0, 0, canvas.width, y + objectMargin);
          });
        }
        // Below image:
        if (spaceBelowOf > minSize) {
          addChoice(() => {
            console.log('bottom');
            zoomIn(0, y + h - objectMargin, canvas.width, spaceBelowOf + objectMargin);
          });
        }
        if (w / 2 > minSize && h / 2 > minSize) {
          addChoice(() => {
            const flexX = w * randomFloat(0.4, 0.6);
            const flexY = h * randomFloat(0.4, 0.6);
            zoomIn(x + randomInt(0, flexX), y + randomInt(0, flexY), w - flexX, h - flexY);
          }, 4);
        }

        if (choices.length > 0) {
          // Zoom away from the detected object:
          chooseRandom(choices)();
          choices = [];

          if (canvas.width === originalW) {
            // Zoomed bottom or top:
            addChoice(() => {
              const factor = canvas.height / originalH;
              let wantedWidth = canvas.width * factor;
              wantedWidth *= randomFloat(0.5, 1.5);
              zoomIn(randomInt(0, canvas.width - wantedWidth), 0, wantedWidth, canvas.height);
            });
          } else if (canvas.height === originalH) {
            // Zoomed left or right:
            addChoice(() => {
              const factor = canvas.width / originalW;
              let wantedHeight = canvas.height * factor;
              wantedHeight *= randomFloat(0.5, 1.5);
              zoomIn(0, randomInt(0, canvas.height - wantedHeight), canvas.width, wantedHeight);
            });
          }

          // Make the image closer in aspect ratio to original:
          if (choices.length > 0) chooseRandom(choices)();

          // Increase the image's size so its closer to what it was:
          scaleSimilarToOriginal();
        } else {
          ctx.fillStyle = 'rgba(255,255,255,1)'
          ctx.fillRect(x, y, w, h);
        }
      } else {
        // No predictions by AI:
        // Zoom in on only part of the image:
        const flexX = canvas.width * randomFloat(0.4, 0.6);
        const flexY = canvas.height * randomFloat(0.4, 0.6);
        zoomIn(randomInt(0, flexX), randomInt(0, flexY), canvas.width - flexX, canvas.height - flexY);

        // Increase the image's size so its closer to what it was:
        scaleSimilarToOriginal();
      }


      withTempCanvas(() => {
        if (!ctx) return true;

        // Rotate it around its middle point
        rotateAround(degreesToRadians(randomInt(1, 360 - 1)), canvas.width / 2, canvas.height / 2);

        // Blur it (30% chance)
        if (Math.random() > 0.7) {
          ctx.filter = `blur(${randomInt(1, 3) * 4}px)`;
        }

        return true;
      });



      // Translate (190/2 is half of the box we drew)
      //ctx.translate(190 / 2, 0);
      // Scale
      //ctx.scale(0.5, 0.5);
    };

    const doEverything = async () => {
      let file: null | File = null;
      try {
        if (!ctx) throw new Error('failed to get 2d context');
        if (!this.selectedFile) return;
        file = this.selectedFile;
        const checkCanceled = () => {
          if (file !== this.selectedFile) throw new Error('Canceled');
        };

        /** Use AI to scan the image, this might take a while. */
        const useAi = true;

        // Start loading AI (this will mostly freeze the browser the first time it is called):
        const modelPromise = useAi ? cocoSsd.load() : null;

        // Load image:
        const imageDataUrl = await blobToDataURL(file);
        checkCanceled();

        // Show a preview of the uploaded image:
        this.previewImageUrl = imageDataUrl;

        const image = await createLoadedImage(imageDataUrl);
        checkCanceled();

        // Wait for AI to finish loading:
        const model = await modelPromise;
        checkCanceled();

        // Run AI on our image:
        const predictions = await model?.detect(image);
        checkCanceled();


        // Log some data:
        console.log(predictions);
        console.log('Image size: ', { width: image.width, height: image.height });
        if (!predictions || predictions.length === 0) {
          console.log("No predictions")
        }


        // Store image data in canvas so we can manipulate it:
        canvas.width = image.width;
        canvas.height = image.height;
        ctx.drawImage(image, 0, 0, image.width, image.height);


        // Rotate, blur and other fun tricks:
        postProcessCanvas(predictions ?? []);


        // Convert canvas to blob:
        const canvasBlob = await canvasToBlob(canvas);
        checkCanceled();



        // Actually save the results from the manipulated canvas:
        this.fileData = canvasBlob;
      } catch (error) {
        console.error('Failed to process image: ', error);
      } finally {
        if (file === this.selectedFile) {
          if (this.fileLoadedCallback !== null) {
            this.fileLoadedCallback();
            this.fileLoadedCallback = null;
          }
        }
      }
    };
    doEverything();
  }
  fileSelect(event: any) {
    this.selectedFile = event.target.files[0] as File;
    this.previewImageUrl = null;

    this.processImage();
  }


  onSubmit(): void {
    // let canvas = document.getElementById('test');
    // let ctx = canvas.getContext('2d');
    // const img = document.getElementById('img');
    // function loadImage(event) {
    //     let reader = new FileReader();
    //     reader.onload = () => {
    //         let output = document.getElementById('img');
    //         output.src = reader.result;
    //         output.setAttribute("width", "200px")
    //         output.setAttribute("height", "200px")
    //         output.onload = function() {
    //             ctx.drawImage(output, 0, 0, 200, 200);
    //         }
    //     }
    //     reader.readAsDataURL(event.target.files[0]);
    //     detect()
    // }

    // function detect() {
    //     // Load model
    //     cocoSsd.load().then(model => {
    //         model.detect(img).then(predictions => {
    //             console.log(predictions)
    //             if(!predictions.length) {
    //                 console.log("No predictions")
    //             }else {
    //                 ctx.fillStyle = 'rgba(255,255,255,1)'
    //                 ctx.fillRect(predictions[0].bbox[0], predictions[0].bbox[1], predictions[0].bbox[2], predictions[0].bbox[3])
    //             }
    //         });
    //     });
    // }
    // Forget previous server errors:
    this.serverErrors = { title: null, body: null };
    this.errorMessage = '';
    this.isSubmitPostFailed = false;

    const { title, body } = this.form;

    const doWork = () => {
      let imageData = null;
      if (this.fileData !== null && this.selectedFile !== null) {
        imageData = this.fileData;
      }
      this.postService.createPost(title, body, imageData).subscribe({
        next: (data) => {
          console.log('Post uploaded successfully: ', data);
          this.isSuccessful = true;
          this.isSubmitPostFailed = false;
        },
        error: (err) => {
          console.log('Post upload failed: ', err);
          this.errorMessage = err.error.message;
          const errors = err.error.errors;
          if (errors) {
            this.serverErrors.title = errors.title || null;
            this.serverErrors.body = errors.body || null;
          }

          this.isSubmitPostFailed = true;
        }
      });
    };
    if (this.selectedFile !== null && this.fileData === null) {
      // Have selected a file and that file is still loading, so wait:
      this.fileLoadedCallback = doWork;
    } else {
      doWork();
    }
  }
}
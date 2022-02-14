import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { PostService } from '../_services/post.service';

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

  fileLoadedCallback: null | (() => void) = null;

  constructor(private postService: PostService) { }

  ngOnInit(): void {
  }

  selectedFile: null | File = null;
  fileData: null | ArrayBuffer = null;
  fileSelect(event: any) {
    this.selectedFile = event.target.files[0] as File;
    // let fileName = this.selectedFile.name;
    let myReader = new FileReader();
    // let fileType = inputValue.parentElement.id;

    myReader.onloadend = () => {
      this.fileData = myReader.result as ArrayBuffer;

      console.log('Loaded file as ArrayBuffer: ' + this.fileData);
      if (this.fileLoadedCallback !== null) {
        this.fileLoadedCallback();
        this.fileLoadedCallback = null;
      }
    };

    myReader.readAsArrayBuffer(this.selectedFile);
  }


  onSubmit(): void {
    // Forget previous server errors:
    this.serverErrors = { title: null, body: null };
    this.errorMessage = '';
    this.isSubmitPostFailed = false;

    const { title, body } = this.form;

    const doWork = () => {
      let imageData = null;
      if (this.fileData !== null && this.selectedFile !== null) {
        imageData = new Blob([this.fileData], { type: this.selectedFile.type });
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
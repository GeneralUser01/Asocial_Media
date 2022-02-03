import { Component, OnInit } from '@angular/core';
import { PostService } from '../_services/post.service';
import { HttpClient } from '@angular/common/http'

@Component({
  selector: 'app-post-creation',
  templateUrl: './post-creation.component.html',
  styleUrls: ['./post-creation.component.css']
})

export class PostCreationComponent implements OnInit {
  form: any = {
    title: null,
    body: null,
    image: null
  };
  isSuccessful = false;
  isSubmitPostFailed = false;
  errorMessage = '';
  serverErrors = { title: null, body: null };

  // selectedFile!: File;

  // onFileChanged(event: { target: { files: File[]; }; }) {
  //   this.selectedFile = event.target.files[0]
  // }

  selectedFile: File | null = null;
  fileName = '';

  fileSelect(event: any) {
    this.selectedFile = <File>event.target.files[0].name;
    console.log('Selected file: ' + this.selectedFile);
  }

  // url: any;
  // msg = "";

  // let reader = new FileReader();
  // reader.readAsDataURL(<File>event.target.files[0]);

  // reader.onload = (_event) => {
  //   this.msg = "";
  //   this.url = reader.result;
  // }

  constructor(private http: HttpClient, private postService: PostService) { }

  ngOnInit(): void {
  }

  onSubmit(): void {
    // Forget previous server errors:
    this.serverErrors = { title: null, body: null };
    this.errorMessage = '';
    this.isSubmitPostFailed = false;

    const { title, body } = this.form;
    this.selectedFile = this.form
    // let image = null;
    // if (this.selectedFile !== null) {
    //   image = new FormData().append('image', this.selectedFile, this.selectedFile.name);
    // }

    this.postService.addPost(title, body, this.selectedFile).subscribe({
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
    //   if (this.selectedFile) {
    //   const formData = new FormData();
    //   formData.append("image", this.selectedFile);
    //   const upload$ = this.http.post("../api/posts/", formData);
    //   upload$.subscribe();
    // }
  }
}

import { Component, OnInit } from '@angular/core';
import { PostService } from '../_services/post.service';

@Component({
  selector: 'app-post-creation',
  templateUrl: './post-creation.component.html',
  styleUrls: ['./post-creation.component.css']
})

export class PostCreationComponent implements OnInit {
  form: any = {
    title: null,
    body: null
  };
  isSuccessful = false;
  isSubmitPostFailed = false;
  errorMessage = '';
  serverErrors = { title: null, body: null };

  selectedFile!: File;

  onFileChanged(event: { target: { files: File[]; }; }) {
    this.selectedFile = event.target.files[0]
  }

  constructor(private postService: PostService) { }

  ngOnInit(): void {
  }

  onSubmit(): void {
    // Forget previous server errors:
    this.serverErrors = { title: null, body: null };
    this.errorMessage = '';
    this.isSubmitPostFailed = false;

    const { title, body } = this.form;

    // uploadData.append('myFile', this.selectedFile, this.selectedFile.name);

    this.postService.addPost(title, body).subscribe({
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
  }
}

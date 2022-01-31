import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-post-creation',
  templateUrl: './post-creation.component.html',
  styleUrls: ['./post-creation.component.css']
})
export class PostCreationComponent implements OnInit {
  form: any = {
    title: null,
    content: null
  };
  isSuccessful = false;
  isSubmitPostFailed = false;
  errorMessage = '';
  serverErrors = { title: null, content: null };

  constructor(private httpClient: HttpClient) { }

  ngOnInit(): void {
  }

  onSubmit(): void {
    // Forget previous server errors:
    this.serverErrors = { title: null, content: null };
    this.errorMessage = '';
    this.isSubmitPostFailed = false;

    const { title, content } = this.form;

    this.httpClient.post(title, content).subscribe({
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
          this.serverErrors.title = errors.name || null;
          this.serverErrors.content = errors.content || null;
        }

        this.isSubmitPostFailed = true;
      }
    });
  }
}

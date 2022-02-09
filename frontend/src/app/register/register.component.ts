import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { AuthService } from '../_services/auth.service';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {
  form: any = {
    username: null,
    email: null,
    password: null,
    passwordConfirmation: null,
  };
  isSuccessful = false;
  isSignUpFailed = false;
  errorMessage = '';
  serverErrors = { username: null, email: null, password: null };

  constructor(private authService: AuthService) { }

  ngOnInit(): void {
  }

  onSubmit(): void {
    // Forget previous server errors:
    this.serverErrors = { username: null, email: null, password: null };
    this.errorMessage = '';
    this.isSignUpFailed = false;

    const { username, email, password } = this.form;

    this.authService.register(username, email, password).subscribe({
      next: (data) => {
        console.log('Registered successfully: ', data);
        this.isSuccessful = true;
        this.isSignUpFailed = false;
      },
      error: (err) => {
        console.log('Registered failed: ', err);
        this.errorMessage = err.error.message;
        const errors = err.error.errors;
        if (errors) {
          this.serverErrors.username = errors.name || null;
          this.serverErrors.email = errors.email || null;
          this.serverErrors.password = errors.password || null;
        }

        this.isSignUpFailed = true;
      }
    });
  }
}

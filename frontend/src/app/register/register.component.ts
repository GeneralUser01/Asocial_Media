import { Component, OnInit } from '@angular/core';
import { AbstractControl } from '@angular/forms';
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
    password_confirmation: null
  };
  isSuccessful = false;
  isSignUpFailed = false;
  password_confirmation = false;
  errorMessage = '';
  serverErrors = { username: null, email: null, password: null, password_confirmation: null };

  constructor(private authService: AuthService) { }

  ngOnInit(): void {
  }

  onSubmit(): void {
    // Forget previous server errors:
    this.serverErrors = { username: null, email: null, password: null, password_confirmation: null };
    this.errorMessage = '';
    this.isSignUpFailed = false;

    const { username, email, password } = this.form;

    (this.password_confirmation === password ? this.password_confirmation = true : this.password_confirmation = false);

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
          this.serverErrors.password_confirmation = errors.password_confirmation || null;
        }

        this.isSignUpFailed = true;
      }
    });
  }
}

import { Component, OnInit } from '@angular/core';
import { AuthService } from '../_services/auth.service';
import { UserService } from '../_services/user.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  form: any = {
    username: null,
    password: null,
    remember: false,
  };
  isLoggedIn = false;
  isLoginFailed = false;
  errorMessage = '';
  roles: string[] = [];
  serverErrors = { username: null, password: null, remember: null, };

  constructor(private authService: AuthService, private userService: UserService) { }

  ngOnInit(): void {
    this.userService.getCurrentUser().subscribe(user => {
      if (!user) return;

      this.isLoggedIn = true;
      //this.roles = user.roles;
    })
  }

  onSubmit(): void {
    // Forget previous server errors:
    this.serverErrors = { username: null, password: null, remember: null };
    this.isLoginFailed = false;
    this.errorMessage = '';

    const { username, password, remember } = this.form;

    this.authService.login(username, password, remember).subscribe({
      next: data => {
        this.isLoginFailed = false;
        this.isLoggedIn = true;
        // this.roles = this.tokenStorage.getUser().roles;
        window.location.reload();
      },
      error: err => {
        this.errorMessage = err.error.message;
        const errors = err.error.errors;
        if (errors && typeof errors === 'object') {

          // Append field errors to total error message:
          for (const errorArray of Object.values(errors)) {
            if (!errorArray || !Array.isArray(errorArray)) {
              continue;
            }
            for (const error of errorArray) {
              if (typeof error !== 'string') continue;
              this.errorMessage += '\n' + error;
            }
          }

          // Show errors next to the fields they are related to (disabled since incorrect password gives an email error):
          //this.serverErrors.username = errors.email || null;
          //this.serverErrors.password = errors.password || null;
          //this.serverErrors.remember = errors.remember || null;
        }

        this.isLoginFailed = true;
      }
    });
  }
}
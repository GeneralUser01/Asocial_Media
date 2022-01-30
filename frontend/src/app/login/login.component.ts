import { Component, OnInit } from '@angular/core';
import { AuthService } from '../_services/auth.service';
import { TokenStorageService } from '../_services/token-storage.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
  form: any = {
    username: null,
    password: null
  };
  isLoggedIn = false;
  isLoginFailed = false;
  errorMessage = '';
  roles: string[] = [];
  serverErrors = { username: null, password: null };

  constructor(private authService: AuthService, private tokenStorage: TokenStorageService) { }

  ngOnInit(): void {
    if (this.tokenStorage.getToken()) {
      this.isLoggedIn = true;
      this.roles = this.tokenStorage.getUser().roles;
    }
  }

  onSubmit(): void {
    // Forget previous server errors:
    this.serverErrors = { username: null, password: null };
    this.isLoginFailed = false;
    this.errorMessage = '';

    const { username, password } = this.form;

    this.authService.login(username, password).subscribe(
      data => {
        this.tokenStorage.saveToken(data.accessToken);
        this.tokenStorage.saveUser(data);

        this.isLoginFailed = false;
        this.isLoggedIn = true;
        this.roles = this.tokenStorage.getUser().roles;
        this.reloadPage();
      },
      err => {
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
        }

        this.isLoginFailed = true;
      }
    );
  }

  reloadPage(): void {
    window.location.reload();
  }
}
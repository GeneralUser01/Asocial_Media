import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { EMPTY, mergeMap, tap } from 'rxjs';
import { RolesService, WithRolesInfo } from '../_services/roles.service';
import { CurrentUser, UserService } from '../_services/user.service';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.css', '../app.component.css'],
  encapsulation: ViewEncapsulation.Emulated
})
export class ProfileComponent implements OnInit {
  currentUser: (CurrentUser & Partial<WithRolesInfo>) | null = null;

  constructor(private userService: UserService, private rolesService: RolesService) { }

  ngOnInit(): void {
    this.userService.getCurrentUser().pipe(
      tap(user => this.currentUser = user),
      mergeMap(user => {
        if (!user) return EMPTY;
        return this.rolesService.getRolesInfo(user);
      }),
    ).subscribe();
  }

}

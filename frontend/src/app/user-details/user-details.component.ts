import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { EMPTY, mergeMap, tap } from 'rxjs';
import { RolesService, WithRolesInfo } from '../_services/roles.service';
import { CurrentUser, User, UserService } from '../_services/user.service';

@Component({
  selector: 'app-user-details',
  templateUrl: './user-details.component.html',
  styleUrls: ['./user-details.component.css']
})
export class UserDetailsComponent implements OnInit {
  currentUser: (CurrentUser & Partial<WithRolesInfo>) | null = null;

  user: (User & Partial<WithRolesInfo>) | null = null;
  userId: null | string = null;

  constructor(
    private userService: UserService,
    private activatedRoute: ActivatedRoute,
    private rolesService: RolesService
  ) { }

  ngOnInit(): void {
    this.userService.getCurrentUser().pipe(
      tap(user => this.currentUser = user),
      mergeMap(user => {
        if (!user) return EMPTY;
        return this.rolesService.getRolesInfo(user);
      }),
    ).subscribe();


    this.activatedRoute.paramMap.subscribe(params => {
      const id = params.get('userId');
      if (!id) return;
      this.userId = id;
      this.updateUser();
    });
  }

  updateUser() {
    if (this.userId === null) return;
    this.userService.getUser(this.userId).pipe(
      tap(user => this.user = user),
      mergeMap(user => {
        if (!user) return EMPTY;
        return this.rolesService.getRolesInfo(user);
      }),
    ).subscribe();
  }

  toggleDisabled() {
    if (this.userId === null) return;
    // TODO: actually get the real disabled id.
    const disabledId = 2;
    if (this.user?.is_disabled) {
      this.rolesService.removeRoleFromUser(disabledId, this.userId).subscribe(() => this.updateUser());
    } else {
      this.rolesService.addRoleToUser(disabledId, this.userId).subscribe(() => this.updateUser());
    }
  }

  toggleAdmin() {
    if (this.userId === null) return;
    // TODO: actually get the real disabled id.
    const adminId = 1;
    if (this.user?.is_admin) {
      this.rolesService.removeRoleFromUser(adminId, this.userId).subscribe(() => this.updateUser());
    } else {
      this.rolesService.addRoleToUser(adminId, this.userId).subscribe(() => this.updateUser());
    }
  }
  deleteUser() {
    if (this.userId === null) return;
    this.userService.deleteUser(this.userId).subscribe(() => this.user = null);
  }
}
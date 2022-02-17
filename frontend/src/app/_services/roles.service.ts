import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { concatMap, last, map, mergeMap, Observable, of, tap } from 'rxjs';
import { Timestamps, WithId, Wrapped, WrappedCollection } from '../_shared/db-types';
import { SimpleRxjsCache } from '../_shared/simple-rxjs-cache';
import { AuthService } from './auth.service';
import { User, UserService } from './user.service';


export interface WithRolesInfo {
  roles_info: Role[],
  is_admin: boolean,
  is_disabled: boolean,
}

export interface RoleContent {
  name: string,
}

export type Role = RoleContent & WithId & Timestamps;

export const ADMIN = 'Administrator';
export const DISABLED = 'Disabled';

@Injectable({
  providedIn: 'root'
})
export class RolesService {
  roleUrl = '../api/roles/';
  httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
  };

  /** Roles shouldn't really change much so should be safe to keep them cached
   * for a long time. */
  roleCache = new SimpleRxjsCache<string, Role>({ maxAge: /* 1h: */ 60 * 60 * 1000, maxEntries: 200 });

  constructor(private http: HttpClient, private authService: AuthService, private userService: UserService) {
    authService.loginStateChanged$.subscribe(() => this.roleCache.clear());
  }

  getRole(roleId: number | string) {
    return this.http.get<Wrapped<Role>>(this.roleUrl + roleId, this.httpOptions)
      .pipe(
        map(result => result.data),
        this.roleCache.rxjsOperator(String(roleId)),
      );
  }
  getRoles(page = 1) {
    return this.http.get<WrappedCollection<Role[]>>(this.roleUrl + '?page=' + page, this.httpOptions)
      // Store the retrieved roles in our cache:
      .pipe(tap(roles => roles.data.forEach(role => this.roleCache.put(String(role.id), role))));
  }

  createRole(role: RoleContent) {
    return this.http.post(this.roleUrl, role, this.httpOptions);
  }
  updateRole(role: Partial<RoleContent> & WithId) {
    return this.http.put(this.roleUrl, role, this.httpOptions)
      .pipe(tap(() => {
        this.roleCache.delete(String(role.id));
        this.userService.clearCache();
      }));
  }
  deleteRole(roleId: number | string) {
    return this.http.delete(this.roleUrl + roleId, this.httpOptions)
      .pipe(tap(() => {
        this.roleCache.delete(String(roleId));
        this.userService.clearCache();
      }));
  }


  private getRoleUserUrl(roleId: string | number) {
    return this.roleUrl + roleId + '/users/';
  }

  getUsersWithRole(roleId: number | string, page = 1) {
    return this.http.get<WrappedCollection<User[]>>(this.getRoleUserUrl(roleId) + '?page=' + page, this.httpOptions);
  }
  addRoleToUser(roleId: number | string, userId: number | string) {
    return this.http.post(this.getRoleUserUrl(roleId), { user_id: userId }, this.httpOptions)
      .pipe(tap(() => this.userService.invalidateCache(userId)));
  }
  removeRoleFromUser(roleId: number | string, userId: number | string) {
    return this.http.delete(this.getRoleUserUrl(roleId) + userId, this.httpOptions)
      .pipe(tap(() => this.userService.invalidateCache(userId)));
  }

  /** Gather roles information for a user. */
  getRolesInfo<T extends User & Partial<WithRolesInfo>>(user: T): Observable<T & WithRolesInfo> {
    // Store roles information in the user:
    const finish = (roles_info: Role[]): T & WithRolesInfo => {
      user.roles_info = roles_info;
      user.is_admin = roles_info.some((role) => role.name === ADMIN);
      user.is_disabled = roles_info.some((role) => role.name === DISABLED);
      return user as T & WithRolesInfo;
    }

    if (!user.roles || user.roles.length === 0) {
      // No roles:
      return of(finish([]));
    };

    if (typeof user.roles[0] !== 'number') {
      // Roles are provided as objects with information already loaded:
      return of(finish(user.roles as Role[]));
    }

    // Roles are role ids:
    const roles = user.roles as number[];
    const roles_info: (null | Role)[] = roles.map(() => null);
    return of(null).pipe(
      // Make one event per role:
      concatMap(() => roles),
      // Get info for each role:
      mergeMap((roleId, index) => {
        return this.getRole(roleId).pipe(map(role => {
          roles_info[index] = role;
        }));
      }),
      // Wait for all:
      last(),
      map(() => {
        for (let i = 0; i < roles_info.length; i++) {
          const role = roles_info[i];
          if (role === null) throw new Error('failed to load info about role with id ' + roles[i] + ' for user with id ' + user.id);
        }
        return finish(roles_info as Role[]);
      }),
    );
  }
}

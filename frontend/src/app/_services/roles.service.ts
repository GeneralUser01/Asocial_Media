import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map } from 'rxjs';
import { Timestamps, WithId, Wrapped, WrappedCollection } from '../_shared/db-types';
import { User } from './user.service';


export interface RoleContent {
  name: string,
}

export type Role = RoleContent & WithId & Timestamps;

@Injectable({
  providedIn: 'root'
})
export class RolesService {
  roleUrl = '../api/roles/';
  httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
  };

  constructor(private http: HttpClient) { }

  getRole(roleId: number | string) {
    return this.http.get<Wrapped<Role>>(this.roleUrl + roleId, this.httpOptions)
      .pipe(map(result => result.data));
  }
  getRoles(page = 1) {
    return this.http.get<WrappedCollection<Role[]>>(this.roleUrl + '?page=' + page, this.httpOptions);
  }

  createRole(role: RoleContent) {
    return this.http.post(this.roleUrl, role, this.httpOptions);
  }
  updateRole(role: Partial<RoleContent> & WithId) {
    return this.http.put(this.roleUrl, role, this.httpOptions);
  }
  deleteRole(roleId: number | string) {
    return this.http.delete(this.roleUrl + roleId, this.httpOptions);
  }


  private getRoleUserUrl(roleId: string | number) {
    return this.roleUrl + roleId + '/users/';
  }

  getUsersWithRole(roleId: number | string, page = 1) {
    return this.http.get<WrappedCollection<User[]>>(this.getRoleUserUrl(roleId) + '?page=' + page, this.httpOptions);
  }
  addRoleToUser(roleId: number | string, userId: number | string) {
    return this.http.post(this.getRoleUserUrl(roleId), { user_id: userId }, this.httpOptions);
  }
  removeRoleFromUser(roleId: number | string, userId: number | string) {
    return this.http.delete(this.getRoleUserUrl(roleId) + userId, this.httpOptions);
  }

  userIsAdmin(user: User): boolean {
    // TODO: User.roles might be objects or role ids, haven't quite decided yet...
    return (user.roles as any).some((role: Role) => typeof role === 'object' && role.name === 'Administrator')
  }
}

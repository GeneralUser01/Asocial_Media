import { Component, OnInit, ViewEncapsulation } from '@angular/core';

@Component({
  selector: 'app-user-details',
  templateUrl: './user-details.component.html',
  styleUrls: ['./user-details.component.css', '../app.component.css'],
  encapsulation: ViewEncapsulation.None
})
export class UserDetailsComponent implements OnInit {

  constructor() { }

  ngOnInit(): void {
  }

}

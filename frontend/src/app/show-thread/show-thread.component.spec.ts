import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ShowThreadComponent } from './show-thread.component';

describe('ShowThreadComponent', () => {
  let component: ShowThreadComponent;
  let fixture: ComponentFixture<ShowThreadComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ShowThreadComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ShowThreadComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

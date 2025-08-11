
import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { EmployeeListComponent } from './components/employee-list/employee-list.component';

// Services
import { ApiService } from './services/api.service';
import { CompanyService } from './services/company.service';
import { DepartmentService } from './services/department.service';
import { EmployeeService } from './services/employee.service';
import { LeaveService } from './services/leave.service';
import { LeaveTypeService } from './services/leave-type.service';

@NgModule({
  declarations: [
    AppComponent,
    DashboardComponent,
    EmployeeListComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    ReactiveFormsModule,
    FormsModule,
    RouterModule
  ],
  providers: [
    ApiService,
    CompanyService,
    DepartmentService,
    EmployeeService,
    LeaveService,
    LeaveTypeService
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }

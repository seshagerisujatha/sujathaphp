
import { Component, OnInit } from '@angular/core';
import { EmployeeService, Employee } from '../../services/employee.service';
import { LeaveService, LeaveRequest } from '../../services/leave.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
  totalEmployees = 0;
  totalLeaves = 0;
  pendingLeaves = 0;
  approvedLeaves = 0;

  constructor(
    private employeeService: EmployeeService,
    private leaveService: LeaveService
  ) {}

  ngOnInit(): void {
    this.loadDashboardData();
  }

  loadDashboardData(): void {
    this.employeeService.getAllEmployees().subscribe(
      (employees: Employee[] | null) => {
        this.totalEmployees = employees ? employees.length : 0;
      }
    );

    this.leaveService.getAllLeaves().subscribe(
      (leaves: LeaveRequest[] | null) => {
        if (leaves) {
          this.totalLeaves = leaves.length;
          this.pendingLeaves = leaves.filter(leave => leave.STATUS === 'Pending').length;
          this.approvedLeaves = leaves.filter(leave => leave.STATUS === 'Approved').length;
        }
      }
    );
  }
}

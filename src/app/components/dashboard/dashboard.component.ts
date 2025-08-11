
import { Component, OnInit } from '@angular/core';
import { EmployeeService, Employee } from '../../services/employee.service';
import { LeaveService, LeaveRequest } from '../../services/leave.service';
import { CompanyService, Company } from '../../services/company.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
  totalEmployees = 0;
  approvedLeaves = 0;
  pendingLeaves = 0;
  totalCompanies = 0;
  recentLeaves: LeaveRequest[] = [];

  constructor(
    private employeeService: EmployeeService,
    private leaveService: LeaveService,
    private companyService: CompanyService
  ) { }

  ngOnInit(): void {
    this.loadDashboardData();
  }

  loadDashboardData(): void {
    // Load total employees
    this.employeeService.getAllEmployees().subscribe({
      next: (employees) => {
        this.totalEmployees = employees ? employees.length : 0;
      },
      error: (error) => console.error('Error loading employees:', error)
    });

    // Load companies
    this.companyService.getAllCompanies().subscribe({
      next: (companies) => {
        this.totalCompanies = companies ? companies.length : 0;
      },
      error: (error) => console.error('Error loading companies:', error)
    });

    // Load leave requests
    this.leaveService.getAllLeaves().subscribe({
      next: (leaves) => {
        if (leaves) {
          this.approvedLeaves = leaves.filter(l => l.STATUS === 'Approved').length;
          this.pendingLeaves = leaves.filter(l => l.STATUS === 'Pending').length;
          this.recentLeaves = leaves.slice(0, 10); // Show last 10 requests
        }
      },
      error: (error) => console.error('Error loading leaves:', error)
    });
  }
}

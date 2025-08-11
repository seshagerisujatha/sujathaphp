
import { Component, OnInit } from '@angular/core';
import { EmployeeService, Employee } from '../../services/employee.service';
import { CompanyService, Company } from '../../services/company.service';
import { DepartmentService, Department } from '../../services/department.service';

@Component({
  selector: 'app-employee-list',
  templateUrl: './employee-list.component.html',
  styleUrls: ['./employee-list.component.css']
})
export class EmployeeListComponent implements OnInit {
  employees: Employee[] = [];
  filteredEmployees: Employee[] = [];
  companies: Company[] = [];
  departments: Department[] = [];
  
  searchTerm = '';
  selectedCompany = '';
  selectedDepartment = '';

  constructor(
    private employeeService: EmployeeService,
    private companyService: CompanyService,
    private departmentService: DepartmentService
  ) { }

  ngOnInit(): void {
    this.loadData();
  }

  loadData(): void {
    this.loadEmployees();
    this.loadCompanies();
    this.loadDepartments();
  }

  loadEmployees(): void {
    this.employeeService.getAllEmployees().subscribe({
      next: (employees) => {
        this.employees = employees || [];
        this.filteredEmployees = [...this.employees];
      },
      error: (error) => console.error('Error loading employees:', error)
    });
  }

  loadCompanies(): void {
    this.companyService.getAllCompanies().subscribe({
      next: (companies) => {
        this.companies = companies || [];
      },
      error: (error) => console.error('Error loading companies:', error)
    });
  }

  loadDepartments(): void {
    this.departmentService.getAllDepartments().subscribe({
      next: (departments) => {
        this.departments = departments || [];
      },
      error: (error) => console.error('Error loading departments:', error)
    });
  }

  filterEmployees(): void {
    this.filteredEmployees = this.employees.filter(employee => {
      const matchesSearch = !this.searchTerm || 
        employee.FNAME.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
        employee.LNAME.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
        employee.EMAIL.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
        employee.EMPLOYID.toLowerCase().includes(this.searchTerm.toLowerCase());

      const matchesCompany = !this.selectedCompany || 
        employee.COMPANYID.toString() === this.selectedCompany;

      const matchesDepartment = !this.selectedDepartment || 
        employee.DEPARTMENTID.toString() === this.selectedDepartment;

      return matchesSearch && matchesCompany && matchesDepartment;
    });
  }

  clearFilters(): void {
    this.searchTerm = '';
    this.selectedCompany = '';
    this.selectedDepartment = '';
    this.filteredEmployees = [...this.employees];
  }

  openAddEmployeeModal(): void {
    // TODO: Implement add employee modal
    console.log('Open add employee modal');
  }

  viewEmployee(employee: Employee): void {
    // TODO: Implement view employee details
    console.log('View employee:', employee);
  }

  editEmployee(employee: Employee): void {
    // TODO: Implement edit employee
    console.log('Edit employee:', employee);
  }

  deleteEmployee(employee: Employee): void {
    if (confirm(`Are you sure you want to delete ${employee.FNAME} ${employee.LNAME}?`)) {
      this.employeeService.deleteEmployee(employee.EMPID!).subscribe({
        next: () => {
          this.loadEmployees();
          console.log('Employee deleted successfully');
        },
        error: (error) => console.error('Error deleting employee:', error)
      });
    }
  }
}

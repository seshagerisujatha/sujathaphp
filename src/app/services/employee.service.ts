
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

/**
 * Employee Interface
 */
export interface Employee {
  EMPID?: number;
  EMPLOYID: string;
  FNAME: string;
  LNAME: string;
  MNAME?: string;
  ADDRESS?: string;
  EMAIL: string;
  PHONE?: string;
  COMPANYID: number;
  DEPARTMENTID: number;
  POSITION?: string;
  DATEHIRED?: string;
  USERNAME: string;
  PASS?: string;
  TYPE?: string;
  STATUS?: string;
  COMPANYNAME?: string;
  DEPARTMENT?: string;
  CREATED_AT?: string;
  UPDATED_AT?: string;
}

/**
 * Employee Service
 * Handles CRUD operations for employees
 */
@Injectable({
  providedIn: 'root'
})
export class EmployeeService {

  constructor(private apiService: ApiService) { }

  /**
   * Get all employees
   */
  getAllEmployees(): Observable<Employee[] | null> {
    return this.apiService.get<Employee[]>('employees.php');
  }

  /**
   * Get employee by ID
   */
  getEmployee(id: number): Observable<Employee | null> {
    return this.apiService.get<Employee>(`employees.php/${id}`);
  }

  /**
   * Create new employee
   */
  createEmployee(employee: Employee): Observable<any> {
    return this.apiService.post('employees.php', employee);
  }

  /**
   * Update employee
   */
  updateEmployee(id: number, employee: Partial<Employee>): Observable<any> {
    return this.apiService.put(`employees.php/${id}`, employee);
  }

  /**
   * Delete employee
   */
  deleteEmployee(id: number): Observable<any> {
    return this.apiService.delete(`employees.php/${id}`);
  }

  /**
   * Search employees by name or employee ID
   */
  searchEmployees(query: string): Observable<Employee[] | null> {
    return this.apiService.get<Employee[]>(`employees.php?search=${encodeURIComponent(query)}`);
  }
}

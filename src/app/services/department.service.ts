
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

/**
 * Department Interface
 */
export interface Department {
  DEPARTMENTID?: number;
  DEPARTMENT: string;
  COMPANYID: number;
  COMPANYNAME?: string;
  CREATED_AT?: string;
  UPDATED_AT?: string;
}

/**
 * Department Service
 * Handles CRUD operations for departments
 */
@Injectable({
  providedIn: 'root'
})
export class DepartmentService {

  constructor(private apiService: ApiService) { }

  /**
   * Get all departments
   */
  getAllDepartments(): Observable<Department[] | null> {
    return this.apiService.get<Department[]>('departments.php');
  }

  /**
   * Get department by ID
   */
  getDepartment(id: number): Observable<Department | null> {
    return this.apiService.get<Department>(`departments.php/${id}`);
  }

  /**
   * Create new department
   */
  createDepartment(department: Department): Observable<any> {
    return this.apiService.post('departments.php', department);
  }

  /**
   * Update department
   */
  updateDepartment(id: number, department: Partial<Department>): Observable<any> {
    return this.apiService.put(`departments.php/${id}`, department);
  }

  /**
   * Delete department
   */
  deleteDepartment(id: number): Observable<any> {
    return this.apiService.delete(`departments.php/${id}`);
  }

  /**
   * Get departments by company
   */
  getDepartmentsByCompany(companyId: number): Observable<Department[] | null> {
    return this.apiService.get<Department[]>(`departments.php?company=${companyId}`);
  }
}

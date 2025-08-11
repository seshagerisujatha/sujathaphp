
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

/**
 * Leave Request Interface
 */
export interface LeaveRequest {
  LEAVEID?: number;
  EMPID: number;
  LEAVETYPEID: number;
  STARTDATE: string;
  ENDDATE: string;
  DAYS?: number;
  REASON: string;
  STATUS?: string;
  APPLIED_DATE?: string;
  APPROVED_BY?: number;
  APPROVED_DATE?: string;
  COMMENTS?: string;
  EMPLOYEE_NAME?: string;
  EMPLOYID?: string;
  LEAVETYPE?: string;
  APPROVED_BY_NAME?: string;
  CREATED_AT?: string;
  UPDATED_AT?: string;
}

/**
 * Leave Service
 * Handles CRUD operations for leave requests
 */
@Injectable({
  providedIn: 'root'
})
export class LeaveService {

  constructor(private apiService: ApiService) { }

  /**
   * Get all leave requests
   */
  getAllLeaves(): Observable<LeaveRequest[] | null> {
    return this.apiService.get<LeaveRequest[]>('leaves.php');
  }

  /**
   * Get leave request by ID
   */
  getLeave(id: number): Observable<LeaveRequest | null> {
    return this.apiService.get<LeaveRequest>(`leaves.php/${id}`);
  }

  /**
   * Create new leave request
   */
  createLeave(leave: LeaveRequest): Observable<any> {
    return this.apiService.post('leaves.php', leave);
  }

  /**
   * Update leave request
   */
  updateLeave(id: number, leave: Partial<LeaveRequest>): Observable<any> {
    return this.apiService.put(`leaves.php/${id}`, leave);
  }

  /**
   * Delete leave request
   */
  deleteLeave(id: number): Observable<any> {
    return this.apiService.delete(`leaves.php/${id}`);
  }

  /**
   * Approve leave request
   */
  approveLeave(id: number, approvedBy: number, comments?: string): Observable<any> {
    const updateData = {
      STATUS: 'Approved',
      APPROVED_BY: approvedBy,
      COMMENTS: comments
    };
    return this.updateLeave(id, updateData);
  }

  /**
   * Reject leave request
   */
  rejectLeave(id: number, approvedBy: number, comments?: string): Observable<any> {
    const updateData = {
      STATUS: 'Rejected',
      APPROVED_BY: approvedBy,
      COMMENTS: comments
    };
    return this.updateLeave(id, updateData);
  }

  /**
   * Get leave requests by employee
   */
  getLeavesByEmployee(employeeId: number): Observable<LeaveRequest[] | null> {
    return this.apiService.get<LeaveRequest[]>(`leaves.php?employee=${employeeId}`);
  }

  /**
   * Get leave requests by status
   */
  getLeavesByStatus(status: string): Observable<LeaveRequest[] | null> {
    return this.apiService.get<LeaveRequest[]>(`leaves.php?status=${encodeURIComponent(status)}`);
  }
}

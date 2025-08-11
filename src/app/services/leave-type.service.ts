
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

/**
 * Leave Type Interface
 */
export interface LeaveType {
  LEAVETYPEID?: number;
  LEAVETYPE: string;
  LEAVEDAYS: number;
  DESCRIPTION?: string;
  CREATED_AT?: string;
  UPDATED_AT?: string;
}

/**
 * Leave Type Service
 * Handles CRUD operations for leave types
 */
@Injectable({
  providedIn: 'root'
})
export class LeaveTypeService {

  constructor(private apiService: ApiService) { }

  /**
   * Get all leave types
   */
  getAllLeaveTypes(): Observable<LeaveType[] | null> {
    return this.apiService.get<LeaveType[]>('leave-types.php');
  }

  /**
   * Get leave type by ID
   */
  getLeaveType(id: number): Observable<LeaveType | null> {
    return this.apiService.get<LeaveType>(`leave-types.php/${id}`);
  }

  /**
   * Create new leave type
   */
  createLeaveType(leaveType: LeaveType): Observable<any> {
    return this.apiService.post('leave-types.php', leaveType);
  }

  /**
   * Update leave type
   */
  updateLeaveType(id: number, leaveType: Partial<LeaveType>): Observable<any> {
    return this.apiService.put(`leave-types.php/${id}`, leaveType);
  }

  /**
   * Delete leave type
   */
  deleteLeaveType(id: number): Observable<any> {
    return this.apiService.delete(`leave-types.php/${id}`);
  }
}


import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

/**
 * Company Interface
 */
export interface Company {
  COMPANYID?: number;
  COMPANYNAME: string;
  COMPANYADDRESS: string;
  COMPANYCONTACTNO: string;
  CREATED_AT?: string;
  UPDATED_AT?: string;
}

/**
 * Company Service
 * Handles CRUD operations for companies
 */
@Injectable({
  providedIn: 'root'
})
export class CompanyService {

  constructor(private apiService: ApiService) { }

  /**
   * Get all companies
   */
  getAllCompanies(): Observable<Company[] | null> {
    return this.apiService.get<Company[]>('companies.php');
  }

  /**
   * Get company by ID
   */
  getCompany(id: number): Observable<Company | null> {
    return this.apiService.get<Company>(`companies.php/${id}`);
  }

  /**
   * Create new company
   */
  createCompany(company: Company): Observable<any> {
    return this.apiService.post('companies.php', company);
  }

  /**
   * Update company
   */
  updateCompany(id: number, company: Partial<Company>): Observable<any> {
    return this.apiService.put(`companies.php/${id}`, company);
  }

  /**
   * Delete company
   */
  deleteCompany(id: number): Observable<any> {
    return this.apiService.delete(`companies.php/${id}`);
  }
}

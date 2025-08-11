
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';

/**
 * API Response Interface
 */
export interface ApiResponse<T = any> {
  status: string;
  message: string;
  data?: T;
  timestamp: string;
}

/**
 * API Service
 * Handles all HTTP requests to the PHP backend
 */
@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private baseUrl = 'http://0.0.0.0:5000/api'; // Replit PHP server
  
  private httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    })
  };

  constructor(private http: HttpClient) { }

  /**
   * Handle HTTP errors
   */
  private handleError(error: HttpErrorResponse): Observable<never> {
    let errorMessage = 'An unknown error occurred';
    
    if (error.error instanceof ErrorEvent) {
      // Client-side error
      errorMessage = `Error: ${error.error.message}`;
    } else {
      // Server-side error
      if (error.error && error.error.message) {
        errorMessage = error.error.message;
      } else {
        errorMessage = `Error Code: ${error.status}\nMessage: ${error.message}`;
      }
    }
    
    console.error('API Error:', errorMessage);
    return throwError(() => new Error(errorMessage));
  }

  /**
   * Process API response
   */
  private processResponse<T>(response: ApiResponse<T>): T | null {
    if (response.status === 'success') {
      return response.data || null;
    } else {
      throw new Error(response.message);
    }
  }

  /**
   * Generic GET request
   */
  get<T>(endpoint: string): Observable<T | null> {
    return this.http.get<ApiResponse<T>>(`${this.baseUrl}/${endpoint}`, this.httpOptions)
      .pipe(
        map(response => this.processResponse(response)),
        catchError(this.handleError)
      );
  }

  /**
   * Generic POST request
   */
  post<T>(endpoint: string, data: any): Observable<T | null> {
    return this.http.post<ApiResponse<T>>(`${this.baseUrl}/${endpoint}`, data, this.httpOptions)
      .pipe(
        map(response => this.processResponse(response)),
        catchError(this.handleError)
      );
  }

  /**
   * Generic PUT request
   */
  put<T>(endpoint: string, data: any): Observable<T | null> {
    return this.http.put<ApiResponse<T>>(`${this.baseUrl}/${endpoint}`, data, this.httpOptions)
      .pipe(
        map(response => this.processResponse(response)),
        catchError(this.handleError)
      );
  }

  /**
   * Generic DELETE request
   */
  delete<T>(endpoint: string): Observable<T | null> {
    return this.http.delete<ApiResponse<T>>(`${this.baseUrl}/${endpoint}`, this.httpOptions)
      .pipe(
        map(response => this.processResponse(response)),
        catchError(this.handleError)
      );
  }
}

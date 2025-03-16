export class AppError extends Error {
    public statusCode: number;
    public errors?: any[];
  
    constructor(message: string, statusCode: number, errors?: any[]) {
      super(message);
      this.statusCode = statusCode;
      this.errors = errors;
    }
  }
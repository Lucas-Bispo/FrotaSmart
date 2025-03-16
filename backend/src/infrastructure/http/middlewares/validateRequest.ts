import { Request, Response, NextFunction } from "express";
import { AnySchema } from "yup";
import { AppError } from "../errors/AppError";


export const validateRequest = (schema: AnySchema) => {
  return async (req: Request, res: Response, next: NextFunction) => {
    try {
      await schema.validate(req.body, { abortEarly: false });
      next();
    } catch (err: any) {
      const errors = err.inner?.map((e: any) => ({ field: e.path, message: e.message })) || [
        { field: "unknown", message: err.message },
      ];
      throw new AppError("Validation failed", 400, errors);
    }
  };
};
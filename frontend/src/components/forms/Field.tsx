import type { InputHTMLAttributes, ReactNode, SelectHTMLAttributes, TextareaHTMLAttributes } from "react";
import { cn } from "@/lib/cn";

const control =
  "w-full border-0 border-b border-navy/20 bg-transparent px-0 py-3 text-base text-ink placeholder:text-muted/60 transition-colors focus:border-navy focus:outline-none focus:ring-0 aria-[invalid=true]:border-red-700";

type BaseProps = { label: string; name: string; error?: string; required?: boolean; hint?: string; className?: string };

export function FieldShell({ label, name, error, required, hint, className, children }: BaseProps & { children: ReactNode }) {
  return (
    <div className={cn("flex flex-col", className)}>
      <label htmlFor={name} className="text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-navy/75">
        {label}
        {required && <span className="ml-1 text-gold" aria-hidden="true">*</span>}
      </label>
      {children}
      {hint && !error && <p className="mt-2 text-xs text-muted">{hint}</p>}
      {error && (
        <p id={`${name}-error`} role="alert" className="mt-2 text-sm text-red-700">
          {error}
        </p>
      )}
    </div>
  );
}

export function TextField({ label, name, error, required, hint, className, ...rest }: BaseProps & InputHTMLAttributes<HTMLInputElement>) {
  return (
    <FieldShell label={label} name={name} error={error} required={required} hint={hint} className={className}>
      <input
        id={name}
        name={name}
        required={required}
        aria-invalid={Boolean(error)}
        aria-describedby={error ? `${name}-error` : undefined}
        className={control}
        {...rest}
      />
    </FieldShell>
  );
}

export function TextAreaField({ label, name, error, required, hint, className, ...rest }: BaseProps & TextareaHTMLAttributes<HTMLTextAreaElement>) {
  return (
    <FieldShell label={label} name={name} error={error} required={required} hint={hint} className={className}>
      <textarea
        id={name}
        name={name}
        required={required}
        aria-invalid={Boolean(error)}
        aria-describedby={error ? `${name}-error` : undefined}
        className={cn(control, "min-h-36 resize-y")}
        {...rest}
      />
    </FieldShell>
  );
}

export function SelectField({
  label,
  name,
  error,
  required,
  hint,
  className,
  options,
  placeholder = "Select…",
  ...rest
}: BaseProps & SelectHTMLAttributes<HTMLSelectElement> & { options: string[]; placeholder?: string }) {
  return (
    <FieldShell label={label} name={name} error={error} required={required} hint={hint} className={className}>
      <select
        id={name}
        name={name}
        required={required}
        aria-invalid={Boolean(error)}
        aria-describedby={error ? `${name}-error` : undefined}
        className={cn(control, "cursor-pointer appearance-none bg-[url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2300294c%22 stroke-width=%221.5%22><path d=%22m6 9 6 6 6-6%22/></svg>')] bg-[length:16px] bg-[right_0_center] bg-no-repeat pr-6")}
        defaultValue=""
        {...rest}
      >
        <option value="" disabled={required}>
          {placeholder}
        </option>
        {options.map((option) => (
          <option key={option} value={option}>
            {option}
          </option>
        ))}
      </select>
    </FieldShell>
  );
}

export function ChoicePills({
  legend,
  name,
  options,
  type,
  error,
  required,
}: {
  legend: string;
  name: string;
  options: string[];
  type: "radio" | "checkbox";
  error?: string;
  required?: boolean;
}) {
  return (
    <fieldset aria-describedby={error ? `${name}-error` : undefined}>
      <legend className="text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-navy/75">
        {legend}
        {required && <span className="ml-1 text-gold" aria-hidden="true">*</span>}
      </legend>
      <div className="mt-4 flex flex-wrap gap-2">
        {options.map((option) => (
          <label key={option} className="cursor-pointer">
            <input type={type} name={name} value={option} className="peer sr-only" />
            <span className="inline-flex items-center border border-navy/20 px-4 py-2.5 text-sm text-navy transition-colors peer-checked:border-navy peer-checked:bg-navy peer-checked:text-white peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-gold hover:border-navy">
              {option}
            </span>
          </label>
        ))}
      </div>
      {error && (
        <p id={`${name}-error`} role="alert" className="mt-2 text-sm text-red-700">
          {error}
        </p>
      )}
    </fieldset>
  );
}

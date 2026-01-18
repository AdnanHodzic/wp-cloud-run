import { cn } from "@/lib/utils";

type SelectProps = {
  options: { value: string, label: string }[];
  value: string;
  onChange: (value: string) => void;
  className?: string;
  id: string;
  disabled?: boolean;
}

const Select = ({ options, value, onChange, className = "", id, disabled = false }: SelectProps) => {

  const handleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    onChange(e.target.value);
  }

  return (
    <select
      disabled={disabled}
      value={value}
      id={id}
      className={cn(
        "h-9 px-2 pr-6 py-1 rounded-md m-0",
        "selection:bg-primary selection:text-primary-foreground",
        "border border-input text-foreground bg-transparent bg-muted",
        "placeholder:text-muted-foreground transition-[color,box-shadow] file:inline-flex md:text-sm",
        "disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50",
        "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
        className
      )} onChange={handleChange}>
      {options.map((option) => (
        <option key={option.value} value={option.value}>{option.label}</option>
      ))}
    </select>
  )
}

export default Select;
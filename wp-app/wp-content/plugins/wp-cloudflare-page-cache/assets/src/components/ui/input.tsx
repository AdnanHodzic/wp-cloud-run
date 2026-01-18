import { cva } from "class-variance-authority"

function Input({ className, type, ...props }: React.ComponentProps<"input">) {
  const inputClasses = cva([
    "h-9 px-3 py-1 rounded-md m-0",
    "selection:bg-primary selection:text-primary-foreground",
    "border border-input text-foreground bg-muted",
    "placeholder:text-muted-foreground transition-[color,box-shadow] file:inline-flex md:text-sm",
    "disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50",
    "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
  ])

  return (
    <input
      type={type}
      data-slot="input"
      className={inputClasses({ className })}
      {...props}
    />
  )
}

export { Input }

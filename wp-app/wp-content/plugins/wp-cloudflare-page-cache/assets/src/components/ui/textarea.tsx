import { cn } from "@/lib/utils"

function Textarea({ className, ...props }: React.ComponentProps<"textarea">) {
  return (
    <textarea
      data-slot="textarea"
      className={cn(
        "px-3 py-2 rounded-md text-sm transition-color",
        "selection:bg-primary selection:text-primary-foreground placeholder:text-muted-foreground",
        "disabled:cursor-not-allowed disabled:opacity-50",
        "bg-muted border border-input text-foreground min-h-16 w-full",
        className
      )}
      {...props}
    />
  )
}

export { Textarea }

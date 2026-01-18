import { cn } from "@/lib/utils"

interface SeparatorProps {
  orientation?: "horizontal" | "vertical"
  className?: string
}

const Separator = ({ orientation = "horizontal", className }: SeparatorProps) => {
  return (
    <div
      className={cn(
        "bg-border shrink-0",
        orientation === "horizontal" ? "h-px w-full" : "w-px h-full min-h-[1rem]",
        className
      )}
    />
  )
}

export default Separator; 
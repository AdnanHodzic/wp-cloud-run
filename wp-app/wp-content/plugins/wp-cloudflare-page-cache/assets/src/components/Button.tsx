import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { cn } from "@/lib/utils"
import { LoaderCircle } from "lucide-react"

const buttonVariants = cva( 
  "border inline-flex items-center justify-center gap-2 rounded font-medium transition-all cursor-pointer disabled:cursor-not-allowed disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0",
  {
    variants: {
      variant: {
        default: "border-primary bg-primary text-primary-foreground shadow-sm hover:bg-primary/90",
        destructive: "border-destructive bg-destructive text-white shadow-sm hover:bg-destructive/90",
        outline: "border-current/25 text-foreground/80 hover:bg-accent hover:text-accent-foreground",
        ghost: "border-transparent hover:bg-accent text-secondary-foreground hover:text-accent-foreground",
        link: "border-transparent text-primary underline-offset-4 hover:underline",
        cta: "border-transparent bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-lg hover:shadow-xl shadow-orange-200 dark:shadow-orange-900 dark:hover:shadow-orange-800 bg-size-[100%] hover:bg-size-[150%] duration-300 ease-in-out",
        orange: "border-orange-600 bg-orange-600 text-white hover:bg-orange-700",
        green: "border-green-600 bg-green-600 text-white shadow-sm hover:bg-green-700",
        blue: "border-blue-600 bg-blue-600 text-white hover:bg-blue-700",
        upsell: "bg-gradient-to-r from-orange-500 to-red-500 shadow-md hover:shadow-lg bg-size-[100%] hover:bg-size-[150%] text-white"
      },
      size: {
        default: "h-10 px-6 py-3 text-sm",
        sm: "h-8 px-4 py-2 text-sm",
        lg: "h-12 px-6 py-3 text-base font-semibold",
        xs: "h-auto px-3 py-1.5 text-xs gap-1",
        icon: "size-9 p-1.5",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)

interface ButtonProps extends VariantProps<typeof buttonVariants> {
  className?: string
  loader?: boolean
  icon?: React.ComponentType
  href?: string
  target?: string
  type?: "button" | "submit"
  disabled?: boolean
  children?: React.ReactNode
  onClick?: () => void
  title?: string
}

const Button = React.forwardRef<HTMLButtonElement | HTMLAnchorElement, ButtonProps>(
  ({ className,
    variant,
    size,
    loader = false,
    icon: Icon,
    href,
    target,
    children,
    onClick,
    ...props
  }, ref) => {
    const Comp = href ? "a" : "button"

    const componentProps = href
      ? { href, target, rel: "noreferrer", ...props }
      : { onClick, ...props }

    return (
      <Comp
        ref={ref}
        className={cn(buttonVariants({ variant, size, className }))}
        {...componentProps}
      >
        {(Icon && !loader) && <Icon />}
        {loader && <LoaderCircle className="animate-spin" />}
        {children}
      </Comp>
    )
  }
)

Button.displayName = "Button"

export default Button;

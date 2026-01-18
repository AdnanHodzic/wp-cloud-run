import { cn } from "@/lib/utils";

type BaseControlProps = {
  id?: string;
  children: React.ReactNode;
  label: string | React.ReactNode;
  description?: string | React.ReactNode;
  stack?: boolean;
  afterControl?: React.ReactNode;
  locked?: boolean;
  afterTitle?: React.ReactNode;
  stackMobile?: boolean;
}

const BaseControl = ({ id = '', children, label, description, stack = true, afterControl = null, locked = false, afterTitle = null, stackMobile=false }: BaseControlProps) => {
  return (
    <div className={cn("p-6", locked && "bg-muted")}>
      <div className={cn("flex items-start justify-between", stackMobile && "flex-col md:flex-row")}>

        <div className="flex-1">
          <div className="flex items-center justify-between gap-3 mb-2">
            {id && (
              <label htmlFor={id}>
                <h3 className="text-sm font-medium">{label}</h3>
              </label>
            )}
            {!id && (
              <div className="text-sm font-medium">{label}</div>
            )}

            {afterTitle}
          </div>
          
          <div className="text-sm text-foreground/80 not-last:mb-3" >
            {description}
          </div>

          {stack && children}

        </div>


        {!stack && (
          <div className={cn({"ml-6": !stackMobile, "md:ml-6 mt-5 md:mt-0": stackMobile})}>
            {children}
          </div>
        )}
      </div>
      {afterControl}
    </div>
  )
}

export default BaseControl;
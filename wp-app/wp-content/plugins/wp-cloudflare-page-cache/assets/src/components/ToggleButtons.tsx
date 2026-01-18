import { cn } from "@/lib/utils";
import { __ } from "@wordpress/i18n";

type ToggleButtonsProps = {
  value: boolean;
  onChange: (value: boolean) => void;
  id: string;
  disabled?: boolean;
  label?: string;
  description?: string;
  labels?: {
    yes: string;
    no: string;
  }
}

const ToggleButtons = ({ value, onChange, id, disabled, labels }: ToggleButtonsProps) => {

  const toggleBaseClasses = cn(
    "px-4 py-2 text-sm font-medium border transition-colors",
    "focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500",
  );

  const activeClasses = (isActive: boolean) => cn({
    "pointer-events-none": disabled,
    "bg-muted-foreground text-white border-muted-foreground": isActive && disabled,
    "bg-primary text-primary-foreground border-primary group-hover:bg-primary/75": isActive && !disabled,
    "bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700": !isActive
  });

  return (
    <div className="flex items-start justify-between">
      <div className="ml-6">
        <button
          id={id}
          disabled={disabled}
          className="group flex rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
          onClick={() => onChange(!value)}
          aria-label={value ? __('Turn off', 'spc') : __('Turn on', 'spc')}
        >
          <div
            className={cn(
              'rounded-l-md',
              toggleBaseClasses,
              activeClasses(value)
            )}
          >
            {labels?.yes || __('Yes', 'spc')}
          </div>
          <div
            className={cn(
              'rounded-r-md',
              toggleBaseClasses,
              activeClasses(!value)
            )}
          >
            {labels?.no || __('No', 'spc')}
          </div>
        </button>
      </div>
    </div>

  )
}

export default ToggleButtons;
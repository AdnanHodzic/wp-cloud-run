import { DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, Dialog as UIDialog } from "@/components/ui/dialog";
import { cn } from "@/lib/utils";
import { useAppStore } from "@/store/store";

type DialogProps = {
  open: boolean;
  onClose: () => void;
  title?: React.ReactNode | string;
  description?: React.ReactNode | string;
  closeButton?: React.ReactNode;
};

const Dialog = ({ open, onClose, title = '', description = '', closeButton = null }: DialogProps) => {
  const { darkMode } = useAppStore();

  const classes = cn('antialiased gap-0', { 'dark': darkMode });

  return (
    <UIDialog open={open} onOpenChange={onClose}>
      <DialogContent className={classes}>
        {title &&
          <DialogHeader>
            <DialogTitle>
              {title}
            </DialogTitle>
          </DialogHeader>
        }

        {description && <DialogDescription dangerouslySetInnerHTML={{ __html: description }} />}

        {closeButton && (<DialogFooter>
          <DialogClose asChild onClick={onClose}>
            {closeButton}
          </DialogClose>
        </DialogFooter>)}
      </DialogContent>
    </UIDialog >
  )
}

export default Dialog;
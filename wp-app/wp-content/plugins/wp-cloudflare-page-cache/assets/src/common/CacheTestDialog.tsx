import Button from "@/components/Button";
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { cn } from "@/lib/utils";
import { useAppStore } from "@/store/store";
import { useCallback } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { CircleCheck, Info } from "lucide-react";
import StatusDialog from "./StatusDialog";

type CacheTestDialogProps = {
  onClose: () => void;
  data: Record<string, any>;
};

const CacheTestDialog = ({ onClose, data }: CacheTestDialogProps) => {
  const { darkMode } = useAppStore();
  const { i18n } = window.SPCDash;
  const classes = cn('antialiased gap-0', { 'dark': darkMode });

  if (!data?.success || !data?.data) {
    return <StatusDialog onClose={onClose} success={data.success} description={data?.message || i18n.genericError} />
  }

  const ModalContent = useCallback(() => {
    const testData = data.data;

    const statuses = [
      {
        status: testData.cloudflare?.status === 'success',
        message: testData.cloudflare?.message
      },
      {
        status: testData.disk_cache?.status === 'success',
        message: testData.disk_cache?.message
      }
    ];

    return (
      <ul className="text-foreground space-y-2">
        {Object.entries(statuses).map(([key, { status, message }]) => {
          const Icon = status ? CircleCheck : Info;

          return (
            <li key={key} className="flex items-center gap-2 text-sm">
              <Icon className={cn("flex items-center justify-center p-1 w-7 h-7 rounded-full", {
                'bg-green-500/20 text-green-600': status,
                'bg-orange-500/20 text-orange-600': !status
              })} />

              <span className="font-medium">
                {message}
              </span>
            </li>
          );
        })
        }
      </ul>
    );
  }, [data.data]);

  return (
    <Dialog open={true} onOpenChange={onClose}>
      <DialogContent className={classes}>

        <DialogHeader className="mb-5">
          <DialogTitle>
            {__('Status', 'wp-cloudflare-page-cache')}
          </DialogTitle>
        </DialogHeader>

        <ModalContent />

        <DialogDescription className="sr-only">
          {__('Status', 'wp-cloudflare-page-cache')}
        </DialogDescription>

        <DialogFooter className="mt-5">
          <DialogClose asChild onClick={onClose}>
            <Button type="button" variant={"orange"}>
              {i18n.close}
            </Button>
          </DialogClose>
        </DialogFooter>

      </DialogContent>
    </Dialog >
  )
}

export default CacheTestDialog;
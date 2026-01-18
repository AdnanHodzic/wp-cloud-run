import { Check, ShieldAlert } from "lucide-react";

import Dialog from "@/common/Dialog";
import Button from "@/components/Button";

type StatusDialogProps = {
  onClose: () => void;
  description: string;
  success?: boolean;
};

const StatusDialog = ({ onClose, description, success = false }: StatusDialogProps) => {
  const { i18n } = window.SPCDash;
  return (
    <Dialog
      open
      onClose={onClose}
      title={(
        <div className="flex items-center justify-start gap-4 mb-5">
          {success && <Check className="size-7 p-1 bg-green-500/25 rounded-full flex items-center justify-center text-green-500" />}
          {!success && <ShieldAlert className="size-7 p-1 bg-destructive/25 rounded-full flex items-center justify-center text-destructive" />}
          <span className="text-base font-semibold">{success ? i18n.success : i18n.error}</span>
        </div>
      )}
      description={description}
      closeButton={(
        <Button type="button" variant={success ? "orange" : "destructive"} className="mt-5">
          {i18n.close}
        </Button>)
      }
    />
  )
}
export default StatusDialog;
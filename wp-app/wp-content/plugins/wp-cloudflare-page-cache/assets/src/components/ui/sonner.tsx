import { useAppStore } from "@/store/store";
import { CheckCircle, CircleAlert, Info, TriangleAlert } from "lucide-react";
import { Toaster as Sonner, ToasterProps } from "sonner";

const Toaster = ({ ...props }: ToasterProps) => {
  const { darkMode } = useAppStore();

  return (
    <Sonner
      position="bottom-right"
      theme={darkMode ? "dark" : "light"}
      richColors
      visibleToasts={5}
      offset={{ bottom: 40, right: 40 }}
      icons={{
        success: <CheckCircle className="size-5" />,
        error: <CircleAlert className="size-5" />,
        warning: <TriangleAlert className="size-5" />,
        info: <Info className="size-5" />,
      }}
      toastOptions={{
        className: "border-current/30",
      }}
      {...props}
    />
  )
}

export { Toaster };


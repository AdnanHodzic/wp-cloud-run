import { cn } from "@/lib/utils";
import { useRef, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { FileText } from "lucide-react";

type FileControlProps = {
  id: string;
  onChange: (content: string | null, filename?: string) => void;
  label?: string | React.ReactNode;
  description?: string | React.ReactNode;
  children?: React.ReactNode;
  disabled?: boolean;
  accept?: string;
  placeholder?: string;
}

const FileControl = ({
  id,
  onChange,
  children,
  accept = ".json",
  placeholder = __("Drop your settings file here or click to browse", "wp-cloudflare-page-cache"),
  disabled = false
}: FileControlProps) => {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isDragOver, setIsDragOver] = useState(false);
  const [selectedFile, setSelectedFile] = useState<string | null>(null);

  const processFile = (file: File) => {
    const reader = new FileReader();

    reader.onload = (event) => {
      const content = event.target?.result as string;
      setSelectedFile(file.name);
      onChange(content, file.name);
    };

    reader.onerror = () => {
      setSelectedFile(null);
      onChange(null);
    };

    reader.readAsText(file);
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];

    if (!file) {
      setSelectedFile(null);
      onChange(null);
      return;
    }

    processFile(file);
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    if (!disabled) {
      setIsDragOver(true);
    }
  };

  const handleDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragOver(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragOver(false);

    if (disabled) return;

    const files = e.dataTransfer.files;
    if (files.length > 0) {
      const file = files[0];

      // Check file type if accept is specified
      if (accept && !file.name.toLowerCase().endsWith(accept.replace('.', ''))) {
        return;
      }

      processFile(file);
    }
  };

  const handleClick = () => {
    if (!disabled && fileInputRef.current) {
      fileInputRef.current.click();
    }
  };

  return (
    <div className="grid gap-3">
      <div
        className={cn(
          "relative w-full min-h-32 border bg-muted border-dashed rounded-md transition-all cursor-pointer group",
          "hover:bg-accent",
          "focus-within:outline-orange-500/50 focus-within:outline-2",
          isDragOver && "border-primary bg-primary/5 ring-primary/20",
          disabled && "opacity-50 cursor-not-allowed hover:border-border hover:bg-transparent",
          selectedFile ? "border-primary/60" : "border-primary/50"
        )}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        onDrop={handleDrop}
        onClick={handleClick}
      >
        <input
          ref={fileInputRef}
          id={id}
          type="file"
          accept={accept}
          onChange={handleFileChange}
          disabled={disabled}
          className="sr-only"
        />

        <div className="flex flex-col items-center justify-center p-4 text-center">
          <>
            <div className="flex items-center justify-center size-12 mb-2 rounded-full bg-background">
              <FileText
                className={cn(
                  "size-6 transition-colors",
                  "group-hover:text-primary",
                  isDragOver ? "text-primary" : "text-muted-foreground",
                )}
              />
            </div>

            <p className="text-sm font-medium text-foreground">{
              selectedFile ? selectedFile : (
                `${placeholder} ${accept && `(${accept.toUpperCase().replace('.', '')})`}`
              )}
            </p>

            {selectedFile && (
              <p className="text-xs text-muted-foreground mt-2">
                {__('Click to replace or drag and drop a new file', 'wp-cloudflare-page-cache')}
              </p>
            )}
          </>

        </div>
      </div>
      {children}
    </div>
  );
}

export default FileControl; 
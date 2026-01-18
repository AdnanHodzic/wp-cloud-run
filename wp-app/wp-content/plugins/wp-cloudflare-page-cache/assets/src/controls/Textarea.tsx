import { useSettingsStore } from "@/store/optionsStore";
import TextareaControl from "@/pages/settings/controls/TextareaControl";
import { useAppStore } from "@/store/store";

type TextareaProps = {
  id: string;
  label: string | React.ReactNode;
  description?: string | React.ReactNode;
  children?: React.ReactNode;
  placeholder?: string;
}

const Textarea = (props: TextareaProps) => {
  const { updateSetting, settings } = useSettingsStore();
  const { asyncLocked } = useAppStore();
  const { id } = props;

  const handleChange = (nextValue: string) => {
    updateSetting(id, nextValue);
  }

  return (
    <TextareaControl
      onChange={handleChange}
      value={settings[id] as string}
      disabled={asyncLocked}
      {...props}
    />
  );
}

export default Textarea;
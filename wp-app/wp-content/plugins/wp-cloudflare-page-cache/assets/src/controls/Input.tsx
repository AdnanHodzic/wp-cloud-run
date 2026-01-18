import { useSettingsStore } from "@/store/optionsStore";
import InputControl from "@/pages/settings/controls/InputControl";
import { useAppStore } from "@/store/store";

type InputProps = {
  id: string;
  label: string | React.ReactNode;
  description?: string | React.ReactNode;
  children?: React.ReactNode;
  placeholder?: string;
  type?: 'text' | 'number';
}

const Input = (props: InputProps) => {
  const { updateSetting, settings } = useSettingsStore();
  const { asyncLocked } = useAppStore();
  const { id } = props;

  const handleChange = (nextValue: string) => {
    updateSetting(id, nextValue);
  }

  return (
    <InputControl
      onChange={handleChange}
      value={settings[id] as string}
      disabled={asyncLocked}
      {...props}
    />
  );
}

export default Input;
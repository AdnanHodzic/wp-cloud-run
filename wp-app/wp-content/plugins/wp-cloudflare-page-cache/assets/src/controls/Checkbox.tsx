import CheckboxControl from "@/pages/settings/controls/CheckboxControl";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";

type CheckboxProps = {
  id: string;
  label: string | React.ReactNode;
  description?: string | React.ReactNode;
  children?: React.ReactNode;
  sideEffectCallback?: (value: boolean) => void;
  disabled?: boolean;
}

const Checkbox = ({ id, disabled = false, ...props }: CheckboxProps) => {
  const { updateSetting, settings } = useSettingsStore();
  const { asyncLocked } = useAppStore();

  const handleChange = (nextValue: boolean) => {
    updateSetting(id, nextValue ? 1 : 0);

    if (props.sideEffectCallback) {
      props.sideEffectCallback(nextValue);
    }
  };

  return (
    <CheckboxControl
      id={id}
      onChange={handleChange}
      value={settings[id] as number}
      disabled={asyncLocked || disabled}
      {...props}
    />
  );
}

export default Checkbox;
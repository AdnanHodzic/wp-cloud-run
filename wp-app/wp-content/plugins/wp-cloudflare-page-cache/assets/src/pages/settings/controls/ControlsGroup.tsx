import BaseControl from "@/pages/settings/controls/BaseControl";
import DummyControl from "@/pages/settings/controls/DummyControl";
import Checkbox from "@/controls/Checkbox";
import Input from "@/controls/Input";
import Textarea from "@/controls/Textarea";
import Toggle from "@/controls/Toggle";
import Separator from "@/common/Separator";
import { __ } from "@wordpress/i18n";
import { cn } from "@/lib/utils";
import Select from "@/controls/Select";

const ControlsGroup = ({ controls }) => {
  return controls
    .filter(({ hide }) => !hide)
    .map((control, idx) => {

      if (!['toggle', 'number', 'text', 'textarea', 'checkbox-group', 'custom', 'select'].includes(control.type)) return null;

      const returnable = [idx !== 0 && <Separator key={`separator-${control.id}`} />];

      if (control.locked) {
        returnable.push(
          <DummyControl
            key={control.id}
            {...control}
          />
        );

        return returnable;
      }

      if (control.type === 'toggle') {
        returnable.push(
          <Toggle
            key={control.id}
            {...control}
          />
        );
      }

      if (control.type === 'number' || control.type === 'text') {
        returnable.push(
          <Input
            key={control.id}
            {...control}
          />
        );
      }

      if (control.type === 'select') {
        returnable.push(
          <Select
            key={control.id}
            {...control}
          />
        );
      }
      if (control.type === 'textarea') {
        returnable.push(
          <Textarea
            key={control.id}
            {...control}
          />
        );
      }

      if (control.type === 'custom') {
        returnable.push(
          <BaseControl
            key={control.id}
            label={control.label}
            description={control.description}
            afterControl={control.children}
            stack={control.stack && control.stack}
          >
            {control.component && control.component}
          </BaseControl>
        )
      }

      if (control.type === 'checkbox-group' && control.controls) {
        returnable.push(
          <BaseControl key={control.id} label={control.label} description={control.description}>
            <div className={cn("grid gap-3 mt-5", !control.stack && "md:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2")}>
              {control.controls.map((ctrl, ctrlIdx) => (
                <div key={ctrl.id} className="flex flex-col gap-3">
                  {(ctrlIdx !== 0 && (control.stack || ctrlIdx !== 1)) && <Separator />}
                  <div className="flex items-end gap-1">
                    <Checkbox
                      id={ctrl.id}
                      label={ctrl.label}
                      description={ctrl.description}
                      disabled={ctrl.disabled || false}
                    />
                    {ctrl.recommended && (
                      <span className="ml-1 text-xs">
                        {` - ${__('Recommended', 'wp-cloudflare-page-cache')}`}
                      </span>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </BaseControl>
        );
      }

      return returnable;
    })
}

export default ControlsGroup;
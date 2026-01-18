import TransitionWrapper from "@/common/TransitionWrapper";
import Button from "@/components/Button";
import Card, { CardContent, CardFooter, CardHeader } from "@/components/Card";
import Container from "@/layout/Container";
import { spcApi } from "@/lib/api";
import { LINKS } from "@/lib/constants";
import { cn } from "@/lib/utils";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { useEffect, useRef, useState, useCallback } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import { RotateCcwIcon, SaveIcon, SettingsIcon } from "lucide-react";
import { toast } from "sonner";

const SidebarQuickActions = () => {
  const { lockAsync, asyncLocked } = useAppStore();
  const [resetting, setResetting] = useState(false);
  const [saving, setSaving] = useState(false);
  const { settings, updateSettings } = useSettingsStore();

  const [bottomBarVisible, setBottomBarVisible] = useState(false);
  const saveButtonRef = useRef(null);

  const debouncedHandleScroll = useCallback(() => {
    if (saveButtonRef.current) {
      const buttonRect = saveButtonRef.current.getBoundingClientRect();
      setBottomBarVisible(buttonRect.bottom < 0 || buttonRect.top > window.innerHeight);
    }
  }, []);

  useEffect(() => {
    let timeoutId: ReturnType<typeof setTimeout>;

    const handleScroll = () => {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(debouncedHandleScroll, 100);
    };

    window.addEventListener('scroll', handleScroll);
    window.addEventListener('resize', handleScroll);

    return () => {
      window.removeEventListener('scroll', handleScroll);
      window.removeEventListener('resize', handleScroll);
      clearTimeout(timeoutId);
    };
  }, [debouncedHandleScroll]);


  const resetSettings = async () => {
    const confirm = window.confirm(__('Are you sure you want reset all?', 'wp-cloudflare-super-page-cache'));

    if (!confirm) {
      return;
    }

    lockAsync(true);
    setResetting(true);

    await spcApi.resetSettings();
    // We do not need to check the response, because Cloudflare will sometimes return errors on reset. 
    // Regardless, all the plugin settings are wiped.
    toast.success(__('Settings have been reset', 'wp-cloudflare-page-cache'), {
      description: __('Please wait, the page will reload soon.', 'wp-cloudflare-page-cache'),
    });

    setTimeout(() => {
      window.location.href = LINKS.MAIN_PAGE;
    }, 3000);
  }
  
  const saveSettings = async () => {
    lockAsync(true);
    setSaving(true);

    const response = await spcApi.updateSettings(settings);

    setSaving(false);
    lockAsync(false);

    if (response.success) {
      toast.success(response.message);

      updateSettings(response.data.settings);

      return;
    }

    toast.error(response.message, {
      description: __('Please try again later.', 'wp-cloudflare-page-cache'),
    });
  }

  return (
    <>
      <Card>
        <CardHeader>
          <h3 className="font-semibold text-base flex items-center">
            <SettingsIcon className="size-4 mr-2 text-orange-600 dark:text-orange-500" />
            {__('Quick Actions', 'wp-cloudflare-page-cache')}
          </h3>
        </CardHeader>

        <CardContent className="p-4 space-y-3">
          <Button
            ref={saveButtonRef}
            variant="orange"
            loader={saving}
            disabled={asyncLocked}
            onClick={saveSettings}
            className="w-full"
            icon={SaveIcon}
          >
            {saving ?
              __('Saving Settings', 'wp-cloudflare-page-cache') + '...' :
              __('Update Settings', 'wp-cloudflare-page-cache')}
          </Button>
          <Button
            variant="outline"
            disabled={asyncLocked}
            loader={resetting}
            onClick={resetSettings}
            className="w-full"
            icon={RotateCcwIcon}
          >
            {resetting ?
              __('Resetting', 'wp-cloudflare-page-cache') + '...' :
              __('Reset All Settings', 'wp-cloudflare-page-cache')}
          </Button>
        </CardContent>

        <CardFooter className="text-xs text-muted-foreground text-center p-0">
          {__('Changes are saved immediately after clicking Update Settings', 'wp-cloudflare-page-cache')}
        </CardFooter>
      </Card>


      <div className={cn("margin-dashboard-menu fixed max-w-full bottom-0 bg-background/90 backdrop-blur right-0 shadow-lg py-2 border-t transition-all duration-300 ease-out z-50",
        bottomBarVisible ? 'translate-y-0 opacity-100' : 'translate-y-full opacity-0'
      )}>
        <Container>
          <div className="flex justify-end gap-2">
            <Button
              size="sm"
              variant="outline"
              className="hidden lg:flex"
              disabled={asyncLocked}
              loader={resetting}
              onClick={resetSettings}
              icon={RotateCcwIcon}
            >
              {resetting ?
                __('Resetting', 'wp-cloudflare-page-cache') + '...' :
                __('Reset All Settings', 'wp-cloudflare-page-cache')}
            </Button>
            <Button
              variant="orange"
              size="sm"
              loader={saving}
              disabled={asyncLocked}
              onClick={saveSettings}
              icon={SaveIcon}
            >
              {saving ?
                __('Saving Settings', 'wp-cloudflare-page-cache') + '...' :
                __('Update Settings', 'wp-cloudflare-page-cache')}
            </Button>
          </div>
        </Container>
      </div>

    </>
  )
}

export default SidebarQuickActions;
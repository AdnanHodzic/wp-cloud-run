import Button from '@/components/Button';
import { useAppStore } from '@/store/store';
import { __ } from '@wordpress/i18n';
import { Moon, Sun } from 'lucide-react';

const DarkModeToggle = () => {
  const { toggleDarkMode, darkMode } = useAppStore();

  return (
    <Button
      variant="ghost"
      size="icon"
      icon={darkMode ? Sun : Moon}
      onClick={toggleDarkMode}
      className="text-muted-foreground"
      title={__('Toggle dark mode', 'wp-cloudflare-page-cache')}
    />
  )
}

export default DarkModeToggle;
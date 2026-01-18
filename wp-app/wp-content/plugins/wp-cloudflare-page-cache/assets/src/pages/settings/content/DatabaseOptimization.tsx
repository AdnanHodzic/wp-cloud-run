
import Select from "@/common/Select";
import TransitionWrapper from "@/common/TransitionWrapper";
import Button from "@/components/Button";
import Card, { CardContent } from "@/components/Card";
import PageContent from "@/layout/PageContent";
import { spcApi } from "@/lib/api";
import { useSettingsStore } from "@/store/optionsStore";
import { useAppStore } from "@/store/store";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Play } from "lucide-react";
import { toast } from "sonner";
import BaseControl from "../controls/BaseControl";
import ControlsGroup from "../controls/ControlsGroup";

export const DatabaseOptimization = () => {
  const { isToggleOn } = useSettingsStore();
  
  const controls = [
    {
      id: 'database_optimization',
      type: 'toggle',
      label: __('Enable Database Optimization', 'wp-cloudflare-page-cache'),
      description: __('Optimize your database by removing unnecessary data, such as post revisions, auto-draft posts, trashed posts, spam comments, and transients.', 'wp-cloudflare-page-cache'),
    },
  ]

  return (
    <PageContent>
      <Card>
        <CardContent className="p-0 divide-y divide-border">
          <ControlsGroup controls={controls} />

          {isToggleOn('database_optimization') && (
            <TransitionWrapper className="divide-y divide-foreground/10">
              <DatabaseOptimizationItem
                title={__('Post Revisions Cleanup', 'wp-cloudflare-page-cache')}
                description={__('Deletes old revisions across all post types (posts, pages, custom post types). WordPress saves a copy every time you edit content, and cleaning them up helps reduce database size.', 'wp-cloudflare-page-cache')}
                scheduleKey="post_revision_interval"
                actionLabel={__('Clean Now', 'wp-cloudflare-page-cache')}
              />
        
              <DatabaseOptimizationItem
                title={__('Auto-Draft Cleanup', 'wp-cloudflare-page-cache')}
                description={__('Deletes all auto-draft entries across all post types (posts, pages, custom post types). Useful for removing leftover drafts WordPress creates automatically.', 'wp-cloudflare-page-cache')}
                scheduleKey="auto_draft_post_interval"
                actionLabel={__('Clean Now', 'wp-cloudflare-page-cache')}
              />
          
              <DatabaseOptimizationItem
                title={__('Trashed Posts Cleanup', 'wp-cloudflare-page-cache')}
                description={__('Permanently deletes all items in the trash, across all post types (posts, pages, custom post types). Helps keep your site clean by removing deleted content right away.', 'wp-cloudflare-page-cache')}
                scheduleKey="trashed_post_interval"
                actionLabel={__('Clean Now', 'wp-cloudflare-page-cache')}
              />
          
              <DatabaseOptimizationItem
                title={__('Spam Comments Cleanup', 'wp-cloudflare-page-cache')}
                description={__('Remove comments marked as spam by your spam filter or manually flagged as spam.', 'wp-cloudflare-page-cache')}
                scheduleKey="spam_comment_interval"
                actionLabel={__('Clean Now', 'wp-cloudflare-page-cache')}
              />
          
              <DatabaseOptimizationItem
                title={__('Trashed Comments Cleanup', 'wp-cloudflare-page-cache')}
                description={__('Permanently deletes all comments that are currently in the trash. This helps keep your site clean by removing deleted comments right away.', 'wp-cloudflare-page-cache')}
                scheduleKey="trashed_comment_interval"
                actionLabel={__('Clean Now', 'wp-cloudflare-page-cache')}
              />
          
              <DatabaseOptimizationItem
                title={__('Transients Data Cleanup', 'wp-cloudflare-page-cache')}
                description={__('Deletes all transients stored in your database. Useful for freeing up space and resetting temporary data.', 'wp-cloudflare-page-cache')}
                scheduleKey="all_transients_interval"
                actionLabel={__('Clean Now', 'wp-cloudflare-page-cache')}
              />
          
              <DatabaseOptimizationItem
                title={__('Database Tables Optimization', 'wp-cloudflare-page-cache')}
                description={__('Runs the OPTIMIZE TABLE command on your database tables to defragment and reclaim unused space. Helps maintain database efficiency and reduce table overhead.', 'wp-cloudflare-page-cache')}
                scheduleKey="optimize_tables_interval"
                actionLabel={__('Optimize Now', 'wp-cloudflare-page-cache')}
              />
            </TransitionWrapper>
          )}
        </CardContent>
      </Card>
    </PageContent>
  );
}

type DatabaseOptimizationItemProps = {
  title: string;
  description: string;
  scheduleKey: string;
  actionLabel: string
}

const DatabaseOptimizationItem = ({ title, description, scheduleKey, actionLabel }: DatabaseOptimizationItemProps) => {
  const { asyncLocked, lockAsync } = useAppStore();
  const { settings, updateSetting } = useSettingsStore();
  const [loading, setLoading] = useState(false);

  const handleScheduleChange = ( value: string ) => {
    updateSetting(scheduleKey, value);
  }

  const optimizeDatabase = async () => {
    lockAsync(true);
    setLoading(true);

    const data = {
      action: scheduleKey,
    }

    const response = await spcApi.databaseOptimization( data );

    if (!response.success) {
      toast.error(response.message);

      return;
    } 
    
    toast.success(response.message);
    
    lockAsync(false);
    setLoading(false);
  }


  const scheduleOptions = Object.entries(window.SPCDash.databaseOptimizationScheduleOptions).map(([key, value]) => ({
    label: value,
    value: key,
  }));


  return (
    <>
      <BaseControl
        label={title}
        description={description}
        afterTitle={
          <Button
            variant="outline"
            size="sm"
            onClick={optimizeDatabase}
            disabled={asyncLocked}
            loader={loading}
            icon={Play}
          >
            {loading ? __('Running', 'wp-cloudflare-page-cache') + '...' : actionLabel}
          </Button>
        }
      >
        <div className="flex items-center space-x-2">
          <label htmlFor={scheduleKey} className="text-sm text-foreground">{__('Schedule:', 'wp-cloudflare-page-cache')}</label>
          <Select
            id={scheduleKey}
            value={settings[scheduleKey]?.toString() || 'never'}
            onChange={handleScheduleChange}
            disabled={asyncLocked}
            options={scheduleOptions}
            className="min-w-[100px]"/>
        </div>
      </BaseControl>
    </>
  );
};

<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Presents the Audit Log Options group.
 *
 * Expects $stateKey.
 *
 * @var string $stateKey The key under which the collapse state is stored.
 * @var bool $collapseable If defined, specifies whether or not this grouping can be collapsed. Defaults to true.
 * @var bool $showControls If defined, specifies whether or not the save/cancel/restore controls are shown. Defaults to false.
 * @var bool $hideShowMenuItem If defined, specifies whether or not the show menu item option is shown. Defaults to false.
 * @var bool $wpTooOld If defined, specifies whether or not controls should be adjusted because the WP version is too old. Defaults to false.
 */

if (!isset($collapseable)) {
	$collapseable = true;
}

if (!isset($showControls)) {
	$showControls = false;
}

if (!isset($hideShowMenuItem)) {
	$hideShowMenuItem = false;
}

if (!isset($wpTooOld)) {
	$wpTooOld = false;
}
?>
<div id="wf-live-traffic-options" class="wf-row">
	<div class="wf-col-xs-12">
		<div class="wf-block<?php if (!$collapseable) { echo ' wf-always-active'; } else { echo (wfPersistenceController::shared()->isActive($stateKey, true) ? ' wf-active' : ''); } ?>" data-persistence-key="<?php echo esc_attr($stateKey); ?>">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php esc_html_e('Audit Log Options', 'wordfence'); ?></strong>
					</div>
					<?php if ($collapseable): ?><div class="wf-block-header-action"><div class="wf-block-header-action-disclosure" role="checkbox" aria-checked="<?php echo (wfPersistenceController::shared()->isActive($stateKey) ? 'true' : 'false'); ?>" tabindex="0"></div></div><?php endif; ?>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix">
				<?php if ($showControls): ?>
				<p>
					<?php echo wp_kses(__('These options let you choose which site events to record in the audit log. When enabled and your site is connected to Wordfence Central, these events are automatically sent to Central to prevent any tampering by an attacker. When <strong>Audit Log logging mode</strong> is set to "Significant Events", all events except content changes will be recorded. "All Events" will include content-related events and may be used to monitor for unauthorized post or page changes. "Preview" and "Disabled" modes do not send any events to Central.', 'wordfence'), array('strong'=>array())); ?>
				</p>
				
				<div class="wf-row">
					<div class="wf-col-xs-12">
						<?php
						echo wfView::create('options/block-controls', array(
							'suppressLogo' => true,
							'restoreDefaultsSection' => wfConfig::OPTIONS_TYPE_AUDIT_LOG,
							'restoreDefaultsMessage' => __('Are you sure you want to restore the default Audit Log settings? This will undo any custom changes you have made to the options on this page.', 'wordfence'),
						))->render();
						?>
					</div>
				</div>
				<?php endif; ?>
				<ul class="wf-block-list">
					<li>
						<?php
						echo wfView::create('options/option-switch', array(
							'optionName' => 'auditLogMode',
							'value' => wfAuditLog::shared()->mode(),
							'title' => __('Audit Log logging mode', 'wordfence'),
							'states' => array(
								array('value' => wfAuditLog::AUDIT_LOG_MODE_DISABLED, 'label' => __('Disabled', 'wordfence')),
								array('value' => wfAuditLog::AUDIT_LOG_MODE_PREVIEW, 'label' => __('Preview', 'wordfence'), 'disabled' => $wpTooOld),
								array('value' => wfAuditLog::AUDIT_LOG_MODE_SIGNIFICANT, 'labelHTML' => wp_kses(__('Significant <span class="wf-hidden-xs">Events</span>', 'wordfence'),array('span' => array('class' => array('wf-hidden-xs')))), 'disabled' => $wpTooOld),
								array('value' => wfAuditLog::AUDIT_LOG_MODE_ALL, 'labelHTML' => wp_kses(__('All <span class="wf-hidden-xs">Events</span>', 'wordfence'),array('span' => array('class' => array('wf-hidden-xs')))), 'disabled' => $wpTooOld),
							),
							'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_TOOLS_AUDIT_LOG_OPTION_MODE),
							'alignment' => 'wf-right',
							'noSpacer' => true,
						))->render();
						?>
					</li>
					<?php if (!$hideShowMenuItem): ?>
					<li>
						<?php
						echo wfView::create('options/option-toggled', array(
							'optionName' => 'displayTopLevelAuditLog',
							'enabledValue' => 1,
							'disabledValue' => 0,
							'value' => wfConfig::get('displayTopLevelAuditLog') ? 1 : 0,
							'title' => __('Display Audit Log menu option', 'wordfence'),
						))->render();
						?>
					</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php
if (!defined('WORDFENCE_VERSION')) { exit; }

$auditLogMode = wfAuditLog::shared()->mode();
$isPaid = wfLicense::current()->isAtLeastPremium();
$centralConnected = wfCentral::isConnected();
$neverEnabled = wfConfig::get('auditLogMode', wfAuditLog::AUDIT_LOG_MODE_DEFAULT) == wfAuditLog::AUDIT_LOG_MODE_DEFAULT && wfLicense::current()->isPaidAndCurrent() && !wfLicense::current()->isAtLeastCare();

require(__DIR__ . '/wfVersionSupport.php'); /** @var $wfFeatureWPVersionAuditLog */
require(ABSPATH . WPINC . '/version.php'); /** @var string $wp_version */
$wpTooOld = version_compare($wp_version, $wfFeatureWPVersionAuditLog, '<');
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Audit Log', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;

			//Hash-based option block linking
			if (window.location.hash) {
				var hashes = WFAD.parseHashes();
				var hash = hashes[hashes.length - 1];
				var block = $('.wf-block[data-persistence-key="' + hash + '"]');
				if (block.length) {
					if (!block.hasClass('wf-active')) {
						block.find('.wf-block-content').slideDown({
							always: function() {
								block.addClass('wf-active');
								$('html, body').animate({
									scrollTop: block.offset().top - 100
								}, 1000);
							}
						});

						WFAD.ajax('wordfence_saveDisclosureState', {name: block.data('persistenceKey'), state: true}, function() {});
					}
					else {
						$('html, body').animate({
							scrollTop: block.offset().top - 100
						}, 1000);
					}

					history.replaceState('', document.title, window.location.pathname + window.location.search);
				}
			}
		});
	})(jQuery);
</script>
<div class="wf-section-title">
	<h2><?php esc_html_e('Audit Log', 'wordfence') ?></h2>
	<span><?php echo wp_kses(sprintf(
		/* translators: URL to support page. */
			__('<a href="%s" target="_blank" rel="noopener noreferrer" class="wf-help-link">Learn more<span class="wf-hidden-xs"> about the Audit Log</span><span class="screen-reader-text"> (opens in new tab)</span></a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_TOOLS_AUDIT_LOG)), array('a'=>array('href'=>array(), 'target'=>array(), 'rel'=>array(), 'class'=>array()), 'span'=>array('class'=>array()))); ?>
		<i class="wf-fa wf-fa-external-link" aria-hidden="true"></i>
	</span>
</div>

<div class="wf-flex-row wf-flex-row-vertical-xs wf-add-bottom-small">
	<div class="wf-flex-row-1 wf-padding-add-bottom">
		<?php esc_html_e("The Wordfence Audit Log records a history of events on your site to assist in monitoring for unauthorized actions or signs of compromise, ranging from user creation and editing to plugin/theme installation and updates. You can choose to log all events or significant events only, which includes all authentication, site configuration, and site functionality events. Events are securely saved to Wordfence Central to prevent any tampering with the data that may interfere with post-incident analysis and response.", 'wordfence') ?>
	</div>
	<?php if (wfCentral::getCentralAuditLogUrl() && !$wpTooOld): ?>
	<div class="wf-flex-row-0 wf-padding-add-left wf-audit-log-controls">
		<a href="<?php echo esc_attr(wfCentral::getCentralAuditLogUrl()); ?>" class="wf-btn wf-btn-primary wf-btn-callout-subtle" id="wf-view-audit-log" role="button" target="_blank" rel="noopener noreferrer"><?php esc_html_e('View Audit Log', 'wordfence'); ?></a>
	</div>
	<?php elseif (!$wpTooOld): ?>
	<div class="wf-flex-row-0 wf-padding-add-left wf-audit-log-controls">
		<a href="<?php echo WORDFENCE_CENTRAL_URL_SEC ?>?newsite=<?php echo esc_attr(home_url()) ?>" class="wf-btn wf-btn-primary wf-btn-callout-subtle" id="wf-connect-audit-log" role="button"><?php esc_html_e('Connect Site', 'wordfence') ?></a>
	</div>
	<?php endif; ?>
</div>

<div class="wordfenceModeElem" id="wordfenceMode_auditLog"></div>

<?php
echo wfView::create('tools/options-group-audit-log', array(
	'stateKey' => 'audit-log-options',
	'showControls' => true,
	'wpTooOld' => $wpTooOld,
))->render();
?>

<?php if ($wpTooOld): ?>
<div id="wordfenceAuditLogWPTooOld">
	<p><strong><?php esc_html_e('Audit log mode: Disabled', 'wordfence') ?>.</strong> <?php esc_html_e(sprintf(
		/* translators: 1. WordPress version. 2. Minimum WordPress version. */
			__('You are running WordPress version %1$s, which is not supported by the Wordfence Audit Log. In order to use it, please upgrade to at least WordPress version %2$s.', 'wordfence'),
			$wp_version,
			$wfFeatureWPVersionAuditLog
		)) ?></p>
</div>
<?php elseif (!$isPaid): ?>
<div id="wordfenceAuditLogPremiumOnly">
	<p><strong><?php esc_html_e('Audit log mode: Not recording', 'wordfence') ?>.</strong> <a href="https://www.wordfence.com/gnl1optionUpgrade/wordfence-signup/" target="_blank" rel="noopener noreferrer" class="wf-premium-link"><?php esc_html_e('Premium Feature', 'wordfence'); ?><span class="screen-reader-text"> (<?php esc_html_e('opens in new tab', 'wordfence') ?>)</span></a></p>
</div>
<?php elseif (!$centralConnected): ?>
<div id="wordfenceAuditLogCentralDisabled">
	<p><strong><?php esc_html_e('Audit log mode: Not recording', 'wordfence') ?>.</strong> <?php esc_html_e('Wordfence Central is not connected, which is required for recording of audit log events.', 'wordfence') ?> <a href="<?php echo WORDFENCE_CENTRAL_URL_SEC ?>?newsite=<?php echo esc_attr(home_url()) ?>" id="wf-connect-audit-log-notice"><?php esc_html_e('Connect Site', 'wordfence') ?></a></p>
</div>
<?php elseif ($auditLogMode == wfAuditLog::AUDIT_LOG_MODE_PREVIEW): ?>
	<div id="wordfenceAuditLogManuallyPreview">
		<p><strong><?php esc_html_e('Audit log mode: Preview', 'wordfence') ?>.</strong> <?php esc_html_e('Change the recording mode setting above to begin recording events to Wordfence Central.', 'wordfence') ?></p>
	</div>
<?php elseif ($auditLogMode == wfAuditLog::AUDIT_LOG_MODE_DISABLED): ?>
<div id="wordfenceAuditLogManuallyDisabled">
	<p><strong><?php esc_html_e('Audit log mode: Disabled', 'wordfence') ?>.</strong> <?php esc_html_e('You will not be able to preview events and events will not record to Wordfence Central.', 'wordfence') ?></p>
</div>
<?php elseif (wfAuditLog::hasOverdueEvents()): ?>
<div id="wordfenceAuditLogOverdue">
	<p><strong><?php esc_html_e('Audit log mode: Malfunctioning', 'wordfence') ?>.</strong> <?php esc_html_e('The Audit Log has failed to successfully send events for two days. Please verify the connection with Wordfence Central, connectivity to the Wordfence servers, and that the database has no damaged tables.', 'wordfence') ?></p>
</div>
<?php elseif ($auditLogMode == wfAuditLog::AUDIT_LOG_MODE_SIGNIFICANT): ?>
<div id="wordfenceAuditLogSignificantOnly">
	<p><strong><?php esc_html_e('Audit log mode: Significant events only', 'wordfence') ?>.</strong> <?php esc_html_e('The audit log is currently recording all significant events to Wordfence Central, which includes user actions and updates, site modifications, and Wordfence configuration changes.', 'wordfence') ?></p>
</div>
<?php else: ?>
<div id="wordfenceAuditLogAll">
	<p><strong><?php esc_html_e('Audit log mode: All events', 'wordfence') ?>.</strong> <?php esc_html_e('The audit log is currently recording all monitored events to Wordfence Central, including content changes, user actions and changes, site modifications, and Wordfence configuration updates.', 'wordfence') ?></p>
</div>
<?php endif; ?>

<?php if ($neverEnabled || $auditLogMode == wfAuditLog::AUDIT_LOG_MODE_DISABLED || !$isPaid): ?>
<div class="wf-flex-row wf-add-bottom-small">
	<div class="wf-flex-row-1">
		<div class="wf-col-xs-12">
			<ul class="wf-flex-vertical wf-audit-log-premium-callout">
				<li><h3><?php esc_html_e('Log Security Events to an Off-Site Audit Log on Wordfence Central', 'wordfence'); ?></h3></li>
				<li><p class="wf-no-top"><?php esc_html_e('The Wordfence Audit Log is designed to monitor all changes and actions in security-sensitive areas of the site. Actions such as user creation, plugin installation and activation, changes to settings, and similar are all logged with relevant contextual information for later review or forensic analysis. Additionally, the log is securely saved outside of the site on Wordfence Central (sample below) to avoid tampering or deletion by malicious actors.', 'wordfence'); ?></p></li>
				<li class="wf-audit-log-preview-wrapper"><img src="<?php echo esc_attr(wfUtils::getBaseURL() . 'images/audit-log-preview.png'); ?>" class="wf-audit-log-preview" alt="Sample records in the Wordfence Audit Log"></li>
				<?php if (!$wpTooOld && !$isPaid): ?>
					<li><a class="wf-btn wf-btn-primary wf-btn-callout-subtle" href="https://www.wordfence.com/gnl1AuditLogUpgrade/wordfence-signup/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Upgrade to Premium', 'wordfence'); ?><span class="screen-reader-text"> (<?php esc_html_e('opens in new tab', 'wordfence') ?>)</span></a></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
<?php endif; ?>
<div id="wf-audit-log" class="wf-row">
	<div class="wf-col-xs-12">
		<div class="wf-block wf-active">
			<div class="wf-block-content">
				<div class="wf-container-fluid wf-padding-no-left wf-padding-no-right">
					<div class="wf-row">
						<div class="<?php echo wfStyle::contentClasses(); ?>">
							<?php if ($auditLogMode != wfAuditLog::AUDIT_LOG_MODE_DISABLED && !$wpTooOld): ?>
							<div class="wf-row">
								<div class="wf-col-xs-12">
									<h3 class="wf-no-bottom"><?php esc_html_e('Recent Event Summary', 'wordfence'); ?></h3>
									<h4 class="wf-h5 wf-add-bottom-small wf-no-top"><?php echo esc_html(preg_replace('#^https?://#', '', wfUtils::wpHomeURL())); ?></h4>
									<p><?php esc_html_e('The most recently-detected events on this site are listed below. When the audit log is enabled and your site is connected to Wordfence Central, full details of each event can be found on Central. This includes information such as record IDs, version numbers, and which modifications were made. Log entries in preview mode are only stored locally.', 'wordfence'); ?></p>
								</div>
							</div>
							<div class="wf-row">
								<div class="wf-col-xs-12">
									<div id="wf-audit-log-recent">
										<table class="wf-striped-table">
											<thead>
											<tr>
												<th><?php esc_html_e('Type', 'wordfence') ?></th>
												<th><?php esc_html_e('Time', 'wordfence') ?></th>
												<th><?php esc_html_e('Events', 'wordfence') ?></th>
											</tr>
											</thead>
											<tbody id="wf-al-listings">
											<?php
											$recent = wfAuditLog::shared()->auditPreview();
											if (empty($recent['requests'])):
											?>
												<tr class="wf-summary-row even">
													<td colspan="3"><?php esc_html_e('No Events Detected', 'wordfence') ?></td>
												</tr>
											<?php
											else: 
												foreach ($recent['requests'] as $i => $request):
											?>
												<tr class="wf-summary-row <?php echo (($i % 2 == 0) ? 'even' : 'odd'); ?>">
													<td class="wf-center">
													<?php foreach ($request['category'] as $c): ?>
														<span class="wf-audit-log-request-type <?php echo wfStyle::auditEventTypeClass($c); ?>"></span>
													<?php endforeach; ?>
													</td>
													<td class="wf-nowrap"><?php echo wfUtils::formatLocalTime('F j, Y', $request['ts']); ?> <span class="wf-visible-xs-inline"><br></span><?php echo wfUtils::formatLocalTime('g:i:s a', $request['ts']); ?></td>
													<td>
														<ul class="wf-audit-log-events">
															<?php foreach ($request['events'] as $e): ?>
															<li><?php echo esc_html($e['name']); ?></li>
															<?php endforeach; ?>
														</ul>
													</td>
												</tr>
											<?php
												endforeach;
											endif;
											?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div class="wf-row">
								<div class="wf-col-xs-12" id="wf-audit-log-legend">
									<ul>
										<li class="<?php echo wfStyle::auditEventTypeClass(wfAuditLog::AUDIT_LOG_CATEGORY_AUTHENTICATION); ?>"><?php esc_html_e('Authentication', 'wordfence') ?></li>
										<li class="<?php echo wfStyle::auditEventTypeClass(wfAuditLog::AUDIT_LOG_CATEGORY_USER_PERMISSIONS); ?>"><?php esc_html_e('User/Permissions', 'wordfence') ?></li>
										<li class="<?php echo wfStyle::auditEventTypeClass(wfAuditLog::AUDIT_LOG_CATEGORY_PLUGINS_THEMES_UPDATES); ?>"><?php esc_html_e('Plugin/Themes/Updates', 'wordfence') ?></li>
										<li class="<?php echo wfStyle::auditEventTypeClass(wfAuditLog::AUDIT_LOG_CATEGORY_FIREWALL); ?>"><?php esc_html_e('Firewall', 'wordfence') ?></li>
										<li class="<?php echo wfStyle::auditEventTypeClass(wfAuditLog::AUDIT_LOG_CATEGORY_SITE_SETTINGS); ?>"><?php esc_html_e('Site Settings', 'wordfence') ?></li>
										<li class="<?php echo wfStyle::auditEventTypeClass(wfAuditLog::AUDIT_LOG_CATEGORY_MULTISITE); ?>"><?php esc_html_e('Multisite', 'wordfence') ?></li>
										<li class="<?php echo wfStyle::auditEventTypeClass(wfAuditLog::AUDIT_LOG_CATEGORY_CONTENT); ?>"><?php esc_html_e('Content', 'wordfence') ?></li>
									</ul>
								</div>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if (wfOnboardingController::willShowNewTour(wfOnboardingController::TOUR_AUDIT_LOG)): ?>
	<script type="application/javascript">
		(function($) {
			$(function() {
				WFAD.tour1 = function() {
					WFAD.tour('wfNewTour1', 'wf-audit-log', 'bottom', 'bottom', null, WFAD.tourComplete);
				};
				WFAD.tourComplete = function() { WFAD.tourFinish('<?php echo esc_attr(wfOnboardingController::TOUR_AUDIT_LOG); ?>'); };
				
				<?php if (wfOnboardingController::shouldShowNewTour(wfOnboardingController::TOUR_AUDIT_LOG)): ?>
				if (!WFAD.isSmallScreen) { WFAD.tour1(); }
				<?php endif; ?>
			});
		})(jQuery);
	</script>

	<script type="text/x-jquery-template" id="wfNewTour1">
		<div>
			<h3><?php esc_html_e('Audit Log', 'wordfence'); ?></h3>
			<p><?php echo wp_kses(__('The Wordfence Audit Log is a premium feature that records a history of events on your site to assist in monitoring for unauthorized actions or signs of compromise. Events can include everything from user creation and editing to plugin/theme installation and updates. All data captured for relevant events is saved remotely to Wordfence Central to prevent any tampering that may interfere with post-incident analysis and response.', 'wordfence'), array('strong'=>array())); ?></p>
			<div class="wf-pointer-footer">
				<ul class="wf-tour-pagination">
					<li class="wf-active">&bullet;</li>
				</ul>
				<div id="wf-tour-continue"><a href="#" class="wf-onboarding-btn wf-onboarding-btn-primary" role="button"><?php esc_html_e('Got it', 'wordfence'); ?></a></div>
			</div>
			<div id="wf-tour-close"><a href="#" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
		</div>
	</script>
<?php endif; ?>

<?php if (wfOnboardingController::willShowUpgradeTour(wfOnboardingController::TOUR_AUDIT_LOG)): ?>
	<script type="application/javascript">
		(function($) {
			$(function() {
				WFAD.tour1 = function() {
					WFAD.tour('wfUpgradeTour1', 'wf-audit-log', 'bottom', 'bottom', null, WFAD.tourComplete);
				};
				WFAD.tourComplete = function() { WFAD.tourFinish('<?php echo esc_attr(wfOnboardingController::TOUR_AUDIT_LOG); ?>'); };
				
				<?php if (wfOnboardingController::shouldShowUpgradeTour(wfOnboardingController::TOUR_AUDIT_LOG)): ?>
				if (!WFAD.isSmallScreen) { WFAD.tour1(); }
				<?php endif; ?>
			});
		})(jQuery);
	</script>

	<script type="text/x-jquery-template" id="wfUpgradeTour1">
		<div>
			<h3><?php esc_html_e('Audit Log', 'wordfence'); ?></h3>
			<p><?php echo wp_kses(__('The Wordfence Audit Log is a premium feature that records a history of events on your site to assist in monitoring for unauthorized actions or signs of compromise. Events can include everything from user creation and editing to plugin/theme installation and updates. All data captured for relevant events is saved remotely to Wordfence Central to prevent any tampering that may interfere with post-incident analysis and response.', 'wordfence'), array('strong'=>array())); ?></p>
			<div class="wf-pointer-footer">
				<ul class="wf-tour-pagination">
					<li class="wf-active">&bullet;</li>
				</ul>
				<div id="wf-tour-continue"><a href="#" role="button" class="wf-onboarding-btn wf-onboarding-btn-primary"><?php esc_html_e('Got it', 'wordfence'); ?></a></div>
			</div>
			<div id="wf-tour-close"><a href="#" role="button"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
		</div>
	</script>
<?php endif; ?>
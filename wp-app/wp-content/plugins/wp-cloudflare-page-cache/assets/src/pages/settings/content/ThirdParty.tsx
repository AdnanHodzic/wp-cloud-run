import { lazy, Suspense } from "@wordpress/element";

import PageContent from "@/layout/PageContent";

const WooCommerce = lazy(() => import("./third-party/WooCommerce"));
const Kinsta = lazy(() => import("./third-party/Kinsta"));
const WPEngine = lazy(() => import("./third-party/WPEngine"));
const Siteground = lazy(() => import("./third-party/Siteground"));
const SwiftPerformance = lazy(() => import("./third-party/SwiftPerformance"));
const YASR = lazy(() => import("./third-party/YASR"));
const WPPerformance = lazy(() => import("./third-party/WPPerformance"));
const NginxHelper = lazy(() => import("./third-party/NginxHelper"));
const WPAssetCleanup = lazy(() => import("./third-party/WPAssetCleanup"));
const WPRocket = lazy(() => import("./third-party/WPRocket"));
const FlyingPress = lazy(() => import("./third-party/FlyingPress"));
const WPOptimize = lazy(() => import("./third-party/WPOptimize"));
const Hummingbird = lazy(() => import("./third-party/Hummingbird"));
const LitespeedCache = lazy(() => import("./third-party/LitespeedCache"));
const W3TC = lazy(() => import("./third-party/W3TC"));
const Autoptimize = lazy(() => import("./third-party/Autoptimize"));
const Edd = lazy(() => import("./third-party/Edd"));
const SpinupWP = lazy(() => import("./third-party/SpinupWP"));


const ThirdParty = () => {
  const availableIntegrations = window.SPCDash.thirdPartyIntegrations;

  return (
    <PageContent>
      {availableIntegrations.woocommerce && (
        <Suspense>
          <WooCommerce />
        </Suspense >
      )}
      {
        availableIntegrations.edd && (
          <Suspense>
            <Edd />
          </Suspense >
        )}
      {
        availableIntegrations.autoptimize && (
          <Suspense>
            <Autoptimize />
          </Suspense >
        )}
      {
        availableIntegrations.w3tc && (
          <Suspense>
            <W3TC />
          </Suspense >
        )}
      {
        availableIntegrations.litespeed_cache && (
          <Suspense>
            <LitespeedCache />
          </Suspense >
        )}
      {
        availableIntegrations.hummingbird && (
          <Suspense>
            <Hummingbird />
          </Suspense >
        )}
      {
        availableIntegrations.wp_optimize && (
          <Suspense>
            <WPOptimize />
          </Suspense >
        )}
      {
        availableIntegrations.flying_press && (
          <Suspense>
            <FlyingPress />
          </Suspense >
        )}
      {
        availableIntegrations.wp_rocket && (
          <Suspense>
            <WPRocket />
          </Suspense >
        )}
      {
        availableIntegrations.wp_asset_cleanup && (
          <Suspense>
            <WPAssetCleanup />
          </Suspense >
        )}
      {
        availableIntegrations.nginx_helper && (
          <Suspense>
            <NginxHelper />
          </Suspense >
        )}
      {
        availableIntegrations.wp_performance && (
          <Suspense>
            <WPPerformance />
          </Suspense >
        )}
      {
        availableIntegrations.yasr && (
          <Suspense>
            <YASR />
          </Suspense >
        )}
      {
        availableIntegrations.swift_performance && (
          <Suspense>
            <SwiftPerformance />
          </Suspense >
        )}
      {
        availableIntegrations.siteground && (
          <Suspense>
            <Siteground />
          </Suspense >
        )}
      {
        availableIntegrations.wp_engine && (
          <Suspense>
            <WPEngine />
          </Suspense >
        )}
      {
        availableIntegrations.spinup_wp && (
          <Suspense>
            <SpinupWP />
          </Suspense >
        )}
      {
        availableIntegrations.kinsta && (
          <Suspense>
            <Kinsta />
          </Suspense >
        )}
    </PageContent>
  )
}


// const CardSkeleton = () => (
//   return <Card></Card>
// }
export default ThirdParty;
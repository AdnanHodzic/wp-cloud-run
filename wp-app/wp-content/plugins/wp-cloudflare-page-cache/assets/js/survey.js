/**
 * Initialize the formbricks survey.
 * 
 * @see https://github.com/formbricks/setup-examples/tree/main/html
 */
window.addEventListener('themeisle:survey:loaded', function () {
    window?.tsdk_formbricks?.init?.({
        environmentId: "clt8lntxw0zbu5zwkn3q2ybkq",
        apiHost: "https://app.formbricks.com",
        ...(window?.swcfpcSurveyData ?? {}),
    });
});
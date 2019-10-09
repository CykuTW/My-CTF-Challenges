using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Reflection;
using System.Web;
using System.Web.Mvc;
using System.Web.Optimization;
using System.Web.Routing;

namespace Donate
{
    public class MvcApplication : System.Web.HttpApplication
    {
        protected void Application_Start()
        {
            AreaRegistration.RegisterAllAreas();
            FilterConfig.RegisterGlobalFilters(GlobalFilters.Filters);
            RouteConfig.RegisterRoutes(RouteTable.Routes);
            BundleConfig.RegisterBundles(BundleTable.Bundles);
            Init_AppDomainAppId();
        }

        protected void Init_AppDomainAppId()
        {
            FieldInfo runtimeInfo = typeof(HttpRuntime).GetField("_theRuntime", BindingFlags.Static | BindingFlags.NonPublic);
            if (runtimeInfo == null) return;
            HttpRuntime theRuntime = (HttpRuntime)runtimeInfo.GetValue(null);
            if (theRuntime == null) return;
            FieldInfo appNameInfo = typeof(HttpRuntime).GetField("_appDomainAppId", BindingFlags.Instance | BindingFlags.NonPublic);
            if (appNameInfo == null) return;
            var appName = (String)appNameInfo.GetValue(theRuntime);
            if (appName != "applicationName") appNameInfo.SetValue(theRuntime, ConfigurationManager.AppSettings["ApplicationName"]);
        }
    }
}

using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Data.Services;

namespace WCFDataServices
{
    public class NorthWindServiceHost : DataServiceHost
    {
        public NorthWindServiceHost(Type serviceType, Uri[] baseAddresses)
                  : base(serviceType, baseAddresses)
        {
            string serviceNamespace = "wcfdataservice";
            string trustedTokenPolicyKey = "CR9S4mi4FudzwffouK6lYepOtdoYqw6tuyJjBpU+8LM=";
            string acsHostName = "accesscontrol.windows.net";

            string trustedAudience = "http://localhost/WCFNorthWindService";
            string requiredClaimType = "all";
            string requiredClaimValue = "true";

            this.Authorization.ServiceAuthorizationManager = new ACSAuthorizationManager(
                                    acsHostName,
                                    serviceNamespace,
                                    trustedAudience,
                                    Convert.FromBase64String(trustedTokenPolicyKey),
                                    requiredClaimType,
                                    requiredClaimValue);
        }


    }
}

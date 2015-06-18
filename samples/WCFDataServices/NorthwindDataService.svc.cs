using System;
using System.Collections.Generic;
using System.Data.Services;
using System.Linq;
using System.ServiceModel.Web;
using System.Web;

namespace WCFDataServices
{
    public class NorthwindDataService : DataService<NorthwindEntities>
    {

        // This method is called only once to initialize service-wide policies.
        public static void InitializeService(DataServiceConfiguration config)
        {
            config.UseVerboseErrors = true;
            // This is only a sample service that does not have any security.
            // Any production service will typically enforce some kind if security.
            config.SetEntitySetAccessRule("*", EntitySetRights.All);
            config.DataServiceBehavior.AcceptCountRequests = true;
            config.DataServiceBehavior.AcceptProjectionRequests = true;
            config.SetEntitySetPageSize("*", 5);
            config.DataServiceBehavior.MaxProtocolVersion = System.Data.Services.Common.DataServiceProtocolVersion.V2;
        }
    }
}

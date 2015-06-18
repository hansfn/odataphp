using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Data.Services;

namespace WCFDataServices
{
    public class NorthWindServiceHostFactory: DataServiceHostFactory
    {
        protected override System.ServiceModel.ServiceHost CreateServiceHost(Type serviceType, Uri[] baseAddresses)
        {
            return new NorthWindServiceHost(serviceType, baseAddresses);
        }
    }
}

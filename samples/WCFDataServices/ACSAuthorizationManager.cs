using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.ServiceModel;
using System.ServiceModel.Web;
using System.Net;

namespace WCFDataServices
{
    public class ACSAuthorizationManager : ServiceAuthorizationManager
    {
            TokenValidator validator;
            string requiredClaimType;
            string requiredClaimValue;

            public ACSAuthorizationManager(string acsHostName, string trustedSolution, string trustedAudienceValue, byte[] trustedSigningKey, string requiredClaimType, string requiredClaimValue)
            {
                this.validator = new TokenValidator(acsHostName, trustedSolution, trustedAudienceValue, trustedSigningKey);
                this.requiredClaimType = requiredClaimType;
                this.requiredClaimValue = requiredClaimValue;
            }

            protected override bool CheckAccessCore(OperationContext operationContext)
            {
                // get the authorization header
                string authorizationHeader = WebOperationContext.Current.IncomingRequest.Headers[HttpRequestHeader.Authorization];

                if (string.IsNullOrEmpty(authorizationHeader))
                {
                    return false;
                }

                // check that it starts with 'WRAP'
                if (!authorizationHeader.StartsWith("WRAP "))
                {
                    return false;
                }

                string[] nameValuePair = authorizationHeader.Substring("WRAP ".Length).Split(new char[] { '=' }, 2);

                if (nameValuePair.Length != 2 ||
                    nameValuePair[0] != "access_token" ||
                    !nameValuePair[1].StartsWith("\"") ||
                    !nameValuePair[1].EndsWith("\""))
                {
                    return false;
                }

                // trim the leading and trailing double-quotes
                string token = nameValuePair[1].Substring(1, nameValuePair[1].Length - 2);

                // validate the token
                if (!this.validator.Validate(token))
                {
                    return false;
                }

                // check for an action claim and get the value
                Dictionary<string, string> claims = this.validator.GetNameValues(token);

                string actionClaimValue;
                if (!claims.TryGetValue(this.requiredClaimType, out actionClaimValue))
                {
                    return false;
                }

                // check for the correct action claim value
                if (!actionClaimValue.Equals(this.requiredClaimValue))
                {
                    return false;
                }

                return true;
            }
    }
}

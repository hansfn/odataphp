<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Default.aspx.cs" Inherits="WCFDataServices.Default" %>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
<head id="Head2" runat="server">
    <title>Sample WCF DataServices</title>
</head>
<body>
    <form id="form1" runat="server">
    <div>
        <asp:HyperLink ID="HyperLink1" runat="server" 
            NavigateUrl="/NorthwindDataService.svc/">Northwind DataService</asp:HyperLink>
    </div>
    <div>
        <asp:HyperLink ID="HyperLink2" runat="server" 
            NavigateUrl="/ACSNorthwindDataService.svc/">Northwind DataService [Access to this service requires ACS authentication]</asp:HyperLink>
    </div>
    <div>
        <asp:HyperLink ID="HyperLink3" runat="server" 
            NavigateUrl="/VideoGameStoreDataService.svc/">Video Game DataService</asp:HyperLink>
    </div>
    </form>
</body>
</html>
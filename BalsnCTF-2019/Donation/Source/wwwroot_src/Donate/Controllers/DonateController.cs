using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Configuration;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Reflection;
using System.Text;
using System.Web;
using System.Web.Mvc;
using System.Web.Script.Serialization;
using Donate.Models;

namespace Donate.Controllers
{
    public class DonateController : Controller
    {
        private static Random random = new Random();
        public static string RandomString(int length)
        {
            const string chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            return new string(Enumerable.Repeat(chars, length).Select(s => s[random.Next(s.Length)]).ToArray());
        }

        private static string ConvertPriceToItemName(int price)
        {
            switch (price)
            {
                case 10000:
                    return "Nintendo Switch";
                case 1000:
                    return "Steak";
                case 100:
                    return "Coffee";
                default:
                    return "Call me daddy";
            }
        }

        private static string ConvertPriceToItemDetail(int price)
        {
            return "Donation";
        }

        public ActionResult Index()
        {
            return View();
        }

        [HttpPost]
        public ActionResult Index(FormCollection post)
        {
            string name = post["name"];
            string phone = post["phone"];
            string email = post["email"];
            int price = int.Parse(post["price"]);

            Receipt receipt = new Receipt()
            {
                Number = RandomString(6),
                Tax = 0,
                ShippingFee = 0,
                Date = DateTime.Now.ToString("yyyy/MM/dd"),
                PaymentDate = DateTime.Now.ToString("yyyy/MM/dd"),
                SubTotal = price,
                Total = price,
                Donor = new Donor()
                {
                    Name = name,
                    Email = email,
                    Phone = phone
                },
                Items = new Item[]
                {
                    new Item()
                    {
                        Name = ConvertPriceToItemName(price),
                        Detail = ConvertPriceToItemDetail(price),
                        Amount = 0,
                        Quantity = 1
                    }
                }
            };
            JavaScriptSerializer serializer = new JavaScriptSerializer();
            string token = serializer.Serialize(receipt);
            token = Convert.ToBase64String(Encoding.UTF8.GetBytes(token));
            return RedirectToAction("Preview", new { token });
        }

        public ActionResult Preview()
        {
            /* TODO: refactor to Single Page Application */
            string token = Request.Params.Get("token");
            bool doDownloadPdf = Request.Params.Get("pdf") != null;

            TempData["token"] = "Bearer " + token;

            if (doDownloadPdf)
            {
                return DownloadPdf();
            }
            else
            {
                return ReceiptDetail();
            }
        }

        public FileResult DownloadPdf()
        {
            string accessToken = Request.Headers.Get("Authorization");
            accessToken = (accessToken == null) ? (string)TempData["token"] : accessToken;
            string receiptUrlForPdf = ConfigurationManager.AppSettings["ReceiptUrlForPdf"];
            string tempFileName = Path.GetTempFileName();

            Process process = new Process();
            process.StartInfo.FileName = ConfigurationManager.AppSettings["Wkhtmltopdf"];
            process.StartInfo.Arguments = String.Format(
                "{0} --custom-header Authorization \"{1}\" {2}",
                receiptUrlForPdf,
                accessToken.Replace("\"", ""),
                tempFileName
            );
            process.StartInfo.UseShellExecute = false;
            process.StartInfo.RedirectStandardInput = true;
            process.StartInfo.RedirectStandardOutput = true;
            process.StartInfo.RedirectStandardError = true;
            process.StartInfo.CreateNoWindow = true;
            process.Start();
            process.WaitForExit();

            byte[] fileBytes = System.IO.File.ReadAllBytes(tempFileName);
            string fileName = "receipt.pdf";
            return File(fileBytes, System.Net.Mime.MediaTypeNames.Application.Octet, fileName);
        }

        public ActionResult ReceiptDetail()
        {
            string accessToken = Request.Headers.Get("Authorization");
            accessToken = (accessToken == null) ? (string)TempData["token"] : accessToken;
            accessToken = accessToken.Substring(7); //skip "Bearer "
            string json = Encoding.UTF8.GetString(Convert.FromBase64String(accessToken));
            JavaScriptSerializer serializer = new JavaScriptSerializer();
            Receipt t = serializer.Deserialize<Receipt>(json);
            TempData["ToPdfUrl"] = Url.Action("Preview", new { token = accessToken, pdf = 1 });
            return View("ReceiptDetail", t);
        }
    }
}
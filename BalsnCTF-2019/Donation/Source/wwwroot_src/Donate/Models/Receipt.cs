using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace Donate.Models
{
    public class Receipt
    {
        public string Number { get; set; }
        public Donor Donor { get; set; }
        public Item[] Items { get; set; }
        public int Tax { get; set; }
        public int ShippingFee { get; set; }
        public int SubTotal { get; set; }
        public int Total { get; set; }
        public string Date { get; set; }
        public string PaymentDate { get; set; }
    }

    public class Donor
    {
        public string Name { get; set; }
        public string Phone { get; set; }
        public string Email { get; set; }
    }

    public class Item
    {
        public string Name { get; set; }
        public string Detail { get; set; }
        public int Quantity { get; set; }
        public int Amount { get; set; }
    }
}
const i=new Intl.NumberFormat("en-PK",{minimumFractionDigits:0,maximumFractionDigits:2});function m(r){const t=Number(r||0);return Number.isFinite(t)?`Rs. ${i.format(t)}`:"Rs. 0"}export{m as f};

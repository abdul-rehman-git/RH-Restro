const priceFormatter = new Intl.NumberFormat("en-PK", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
});

export function formatPrice(value) {
    const amount = Number(value || 0);

    if (!Number.isFinite(amount)) {
        return "Rs. 0";
    }

    return `Rs. ${priceFormatter.format(amount)}`;
}

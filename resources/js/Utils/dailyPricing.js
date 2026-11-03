export function calcDailyBaseCost(days, daily, monthly, extraHours = 0) {
    if (days <= 0) return 0;
    let base;
    if (!monthly || monthly >= 28 * daily) {
        base = days * daily;
    } else if (monthly * 0.775 > 4 * daily) {
        if (days <= 4) base = days * daily;
        else if (days <= 7) base = 4 * daily + (days - 4) / 3 * (monthly * 0.775 - 4 * daily);
        else if (days <= 28) base = monthly * (0.775 + (days - 7) / 21 * 0.225);
        else base = monthly + (days - 28) * monthly / 28;
    } else {
        const k = Math.max(1, Math.min(3, Math.floor((monthly - 1) / Math.max(0.01, daily))));
        if (days <= k) base = days * daily;
        else if (days <= 28) base = k * daily + (days - k) / (28 - k) * (monthly - k * daily);
        else base = monthly + (days - 28) * monthly / 28;
    }
    return base * (1 + Math.max(0, extraHours) / 12);
}

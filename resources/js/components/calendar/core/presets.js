import DateValue from './DateValue';

const t = () => DateValue.today();

const PRESET_HANDLERS = {
    today: () => {
        const d = t();
        return [d, d];
    },

    yesterday: () => {
        const d = t().subDay();
        return [d, d];
    },

    this_week: () => {
        const d = t();
        const start = d.subDays((d.getDayOfWeek() + 6) % 7);
        return [start, d];
    },

    last_week: () => {
        const base = t().subWeek();
        const start = base.subDays((base.getDayOfWeek() + 6) % 7);
        return [start, start.addDays(6)];
    },

    this_month: () => {
        const d = t();
        return [d.startOfMonth(), d];
    },

    last_month: () => {
        const d = t();
        const start = d.subMonth().startOfMonth();
        const end = d.startOfMonth().subDay();
        return [start, end];
    },

    this_quarter: () => {
        const d = t();
        const m = Math.floor((d.getMonth() - 1) / 3) * 3 + 1;
        return [new DateValue(d.getYear(), m, 1), d];
    },

    last_quarter: () => {
        const d = t().subMonths(3);
        const m = Math.floor((d.getMonth() - 1) / 3) * 3 + 1;
        const start = new DateValue(d.getYear(), m, 1);
        return [start, start.addMonths(3).subDay()];
    },

    this_year: () => {
        const d = t();
        return [new DateValue(d.getYear(), 1, 1), d];
    },

    last_year: () => {
        const d = t();
        return [
            new DateValue(d.getYear() - 1, 1, 1),
            new DateValue(d.getYear(), 1, 1).subDay(),
        ];
    },

    last_3_days: () => {
        const d = t();
        return [d.subDays(3), d];
    },

    last_7_days: () => {
        const d = t();
        return [d.subDays(7), d];
    },

    last_14_days: () => {
        const d = t();
        return [d.subDays(14), d];
    },

    last_30_days: () => {
        const d = t();
        return [d.subDays(30), d];
    },

    last_90_days: () => {
        const d = t();
        return [d.subDays(90), d];
    },

    last_3_months: () => {
        const d = t();
        return [d.subMonths(3), d];
    },

    last_6_months: () => {
        const d = t();
        return [d.subMonths(6), d];
    },

    year_to_date: () => {
        const d = t();
        return [new DateValue(d.getYear(), 1, 1), d];
    },

    last_week_to_date: () => {
        const d = t();
        const base = d.subWeek();
        const start = base.subDays((base.getDayOfWeek() + 6) % 7);
        return [start, d];
    },

    last_month_to_date: () => {
        const d = t();
        return [d.subMonth().startOfMonth(), d];
    },

    last_quarter_to_date: () => {
        const d = t().subMonths(3);
        const m = Math.floor((d.getMonth() - 1) / 3) * 3 + 1;
        const start = new DateValue(d.getYear(), m, 1);
        return [start, t()];
    },

    next_7_days: () => {
        const d = t();
        return [d, d.addDays(7)];
    },

    next_30_days: () => {
        const d = t();
        return [d, d.addDays(30)];
    },

    next_month: () => {
        const d = t().addMonth().startOfMonth();
        return [d, d.addMonth().subDay()];
    },

    next_quarter: () => {
        const d = t().addMonths(3);
        const m = Math.floor((d.getMonth() - 1) / 3) * 3 + 1;
        const start = new DateValue(d.getYear(), m, 1);
        return [start, start.addMonths(3).subDay()];
    },

    next_year: () => {
        const d = t();
        return [
            new DateValue(d.getYear() + 1, 1, 1),
            new DateValue(d.getYear() + 2, 1, 1).subDay(),
        ];
    },
    custom: () => null,
};

export default PRESET_HANDLERS;

export function isValidPreset(key) {
    return Object.hasOwnProperty.call(PRESET_HANDLERS, key);
}
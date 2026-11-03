import test from 'node:test';
import assert from 'node:assert/strict';
import { calcDailyBaseCost } from '../../resources/js/Utils/dailyPricing.js';

test('daily price anchors, interpolation, and increasing totals', () => {
    for (const [days, amount] of [[1,12],[2,24],[3,36],[4,48],[5,52.67],[6,57.33],[7,62],[14,68],[21,74],[28,80]]) {
        assert.equal(Number(calcDailyBaseCost(days,12,80).toFixed(2)), amount);
    }
    for (let day=1; day<60; day++) assert.ok(calcDailyBaseCost(day+1,12,80) > calcDailyBaseCost(day,12,80));
    assert.equal(calcDailyBaseCost(5,12,0),60);
    assert.equal(calcDailyBaseCost(28,12,80,12),160);
});

test('minimum monthly limited price smoothly increases and reaches anchor', () => {
    for (let day=1; day<60; day++) {
        assert.ok(calcDailyBaseCost(day+1,12,45) > calcDailyBaseCost(day,12,45));
    }
    assert.equal(Number(calcDailyBaseCost(28,12,45).toFixed(2)), 45);
    assert.equal(Number(calcDailyBaseCost(28,12,50).toFixed(2)), 50);
});

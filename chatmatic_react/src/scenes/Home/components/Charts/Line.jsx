import React from 'react';
import { LineChart } from 'react-chartkick';
import 'chart.js';

export default function({ data }) {
    return <LineChart data={data} />;
}

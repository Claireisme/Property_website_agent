<x-layouts.site :agency="$agency" :title="__('site.nav.mortgages')">
    <section class="hero">
        <div class="shell hero-grid">
            <div>
                <span class="badge">{{ __('site.nav.mortgages') }}</span>
                <h1>Buying budget calculator</h1>
                <p>Estimate your purchase budget before you book viewings. This is a planning guide only and not mortgage advice.</p>
            </div>
            <div class="panel">
                <strong>Prepare before you bid</strong>
                <p class="lead" style="margin-bottom: 0;">Use income, deposit, commitments, term, and rate to sketch a sensible borrowing range.</p>
            </div>
        </div>
    </section>

    <section class="band">
        <div class="shell" style="display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 28px;">
            <article class="card">
                <div class="card-body">
                    <h2>Your finances</h2>
                    <form class="form" data-mortgage-calculator>
                        <label>Annual income
                            <input name="income" type="number" min="0" value="75000">
                        </label>
                        <label>Second income
                            <input name="second_income" type="number" min="0" value="0">
                        </label>
                        <label>Deposit available
                            <input name="deposit" type="number" min="0" value="60000">
                        </label>
                        <label>Monthly commitments
                            <input name="commitments" type="number" min="0" value="350">
                        </label>
                        <label>Mortgage term
                            <select name="term">
                                @foreach ([20, 25, 30, 35] as $term)
                                    <option value="{{ $term }}" @selected($term === 30)>{{ $term }} years</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Interest rate
                            <input name="rate" type="number" min="0" step="0.05" value="4.25">
                        </label>
                        <button class="span-2" type="submit">Calculate budget</button>
                    </form>
                </div>
            </article>

            <aside class="card">
                <div class="card-body">
                    <h2>Estimate</h2>
                    <div class="detail-copy">
                        <div>
                            <span class="muted">Borrowing range</span>
                            <div class="price" data-borrowing-range>EUR 0 - EUR 0</div>
                        </div>
                        <div>
                            <span class="muted">Indicative buying budget</span>
                            <div class="price" data-total-budget>EUR 0</div>
                        </div>
                        <div>
                            <span class="muted">Estimated monthly repayment</span>
                            <div class="price" data-monthly-payment>EUR 0</div>
                        </div>
                        <div>
                            <span class="muted">Estimated LTV</span>
                            <div class="price" data-ltv>0%</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="band" style="padding-top: 0;">
        <div class="shell">
            <div class="grid">
                <article class="card"><div class="card-body"><strong>Income reference</strong><p class="muted">A common affordability guide is around 3.5x to 4.5x household income before lender checks.</p></div></article>
                <article class="card"><div class="card-body"><strong>Deposit context</strong><p class="muted">A larger deposit generally lowers loan-to-value and can improve product choice.</p></div></article>
                <article class="card"><div class="card-body"><strong>Next step</strong><p class="muted">When ready, request an Approval in Principle from a regulated mortgage adviser or lender.</p></div></article>
            </div>
        </div>
    </section>

    <script>
        (() => {
            const form = document.querySelector('[data-mortgage-calculator]');
            if (! form) return;
            const euro = (value) => `EUR ${Math.max(0, Math.round(value)).toLocaleString()}`;
            const output = {
                range: document.querySelector('[data-borrowing-range]'),
                budget: document.querySelector('[data-total-budget]'),
                monthly: document.querySelector('[data-monthly-payment]'),
                ltv: document.querySelector('[data-ltv]'),
            };

            const calculate = () => {
                const data = new FormData(form);
                const income = Number(data.get('income') || 0) + Number(data.get('second_income') || 0);
                const deposit = Number(data.get('deposit') || 0);
                const commitments = Number(data.get('commitments') || 0);
                const term = Number(data.get('term') || 30);
                const rate = Number(data.get('rate') || 4.25) / 100 / 12;
                const conservative = Math.max(0, income * 3.5 - commitments * 12);
                const optimistic = Math.max(conservative, income * 4.5 - commitments * 12);
                const principal = (conservative + optimistic) / 2;
                const months = term * 12;
                const monthly = rate ? principal * (rate * Math.pow(1 + rate, months)) / (Math.pow(1 + rate, months) - 1) : principal / months;
                const budget = optimistic + deposit;
                const ltv = budget > 0 ? (optimistic / budget) * 100 : 0;

                output.range.textContent = `${euro(conservative)} - ${euro(optimistic)}`;
                output.budget.textContent = euro(budget);
                output.monthly.textContent = euro(monthly);
                output.ltv.textContent = `${Math.round(ltv)}%`;
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                calculate();
            });
            form.addEventListener('input', calculate);
            calculate();
        })();
    </script>
</x-layouts.site>

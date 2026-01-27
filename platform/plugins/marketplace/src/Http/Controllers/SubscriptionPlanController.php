<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Supports\Breadcrumb;
use Botble\Marketplace\Forms\SubscriptionPlanForm;
use Botble\Marketplace\Http\Requests\SubscriptionPlanRequest;
use Botble\Marketplace\Models\SubscriptionPlan;
use Botble\Marketplace\Tables\SubscriptionPlanTable;
use Illuminate\Http\Request;

class SubscriptionPlanController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/marketplace::subscription-plan.name'), route('marketplace.subscription-plans.index'));
    }

    public function index(SubscriptionPlanTable $table)
    {
        $this->pageTitle(trans('plugins/marketplace::subscription-plan.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/marketplace::subscription-plan.create'));

        return SubscriptionPlanForm::create()->renderForm();
    }

    public function store(SubscriptionPlanRequest $request)
    {
        $plan = SubscriptionPlan::query()->create($request->validated());

        if ($request->input('is_default')) {
            SubscriptionPlan::query()
                ->where('id', '!=', $plan->id)
                ->update(['is_default' => false]);
        }

        event(new CreatedContentEvent(SUBSCRIPTION_PLAN_MODULE_SCREEN_NAME, $request, $plan));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('marketplace.subscription-plans.index'))
            ->setNextUrl(route('marketplace.subscription-plans.edit', $plan->id))
            ->withCreatedSuccessMessage();
    }

    public function edit(int|string $id)
    {
        $plan = SubscriptionPlan::query()->findOrFail($id);

        $this->pageTitle(trans('plugins/marketplace::subscription-plan.edit', ['name' => $plan->name]));

        return SubscriptionPlanForm::createFromModel($plan)->renderForm();
    }

    public function update(int|string $id, SubscriptionPlanRequest $request)
    {
        $plan = SubscriptionPlan::query()->findOrFail($id);

        $plan->update($request->validated());

        if ($request->input('is_default')) {
            SubscriptionPlan::query()
                ->where('id', '!=', $plan->id)
                ->update(['is_default' => false]);
        }

        event(new UpdatedContentEvent(SUBSCRIPTION_PLAN_MODULE_SCREEN_NAME, $request, $plan));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('marketplace.subscription-plans.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(int|string $id, Request $request)
    {
        $plan = SubscriptionPlan::query()->findOrFail($id);

        return DeleteResourceAction::make($plan);
    }
}

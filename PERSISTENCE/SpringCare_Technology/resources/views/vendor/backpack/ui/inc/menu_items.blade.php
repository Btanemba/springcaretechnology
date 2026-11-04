{{-- This file is used for menu items by any Backpack v7 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="la la-user-tag nav-icon"></i> Roles</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('category') }}"><i class="la la-tags nav-icon"></i> Categories</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('brand') }}"><i class="la la-trademark nav-icon"></i> Brands</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('product') }}"><i class="la la-box nav-icon"></i> Products</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('product-unit') }}"><i class="la la-barcode nav-icon"></i> Product Units</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('delivery-schedule') }}"><i class="la la-truck nav-icon"></i> Delivery Schedule</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('admin.academy-advertisement.edit') }}"><i class="la la-bullhorn nav-icon"></i> Academy Advert</a></li>
<li class="nav-item"><a class="nav-link text-danger" href="{{ backpack_url('logout') }}"><i class="la la-sign-out-alt nav-icon"></i> {{ trans('backpack::base.logout') }}</a></li>

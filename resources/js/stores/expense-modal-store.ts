import { create } from "zustand";

type ExpenseModalStore = {
    open: boolean
    openCreateModal: () => void
    closeModal: () => void
}

export const useExpenseModalStore = create<ExpenseModalStore>((set) => ({
    open: false,
    openCreateModal: () => {
        set({
            open: true
        })
    },
    closeModal: () => {
        set({
            open: false
        })
    },
}))